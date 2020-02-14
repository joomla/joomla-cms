<?php

namespace tubalmartin\CssMin;

class Command
{
    const SUCCESS_EXIT = 0;
    const FAILURE_EXIT = 1;
    
    protected $stats = array();
    
    public static function main()
    {
        $command = new self;
        $command->run();
    }

    public function run()
    {
        $opts = getopt(
            'hi:o:',
            array(
                'help',
                'input:',
                'output:',
                'dry-run',
                'keep-sourcemap',
                'keep-sourcemap-comment',
                'linebreak-position:',
                'memory-limit:',
                'pcre-backtrack-limit:',
                'pcre-recursion-limit:',
                'remove-important-comments'
            )
        );

        $help = $this->getOpt(array('h', 'help'), $opts);
        $input = $this->getOpt(array('i', 'input'), $opts);
        $output = $this->getOpt(array('o', 'output'), $opts);
        $dryrun = $this->getOpt('dry-run', $opts);
        $keepSourceMapComment = $this->getOpt(array('keep-sourcemap', 'keep-sourcemap-comment'), $opts);
        $linebreakPosition = $this->getOpt('linebreak-position', $opts);
        $memoryLimit = $this->getOpt('memory-limit', $opts);
        $backtrackLimit = $this->getOpt('pcre-backtrack-limit', $opts);
        $recursionLimit = $this->getOpt('pcre-recursion-limit', $opts);
        $removeImportantComments = $this->getOpt('remove-important-comments', $opts);

        if (!is_null($help)) {
            $this->showHelp();
            die(self::SUCCESS_EXIT);
        }

        if (is_null($input)) {
            fwrite(STDERR, '-i <file> argument is missing' . PHP_EOL);
            $this->showHelp();
            die(self::FAILURE_EXIT);
        }

        if (!is_readable($input)) {
            fwrite(STDERR, 'Input file is not readable' . PHP_EOL);
            die(self::FAILURE_EXIT);
        }

        $css = file_get_contents($input);

        if ($css === false) {
            fwrite(STDERR, 'Input CSS code could not be retrieved from input file' . PHP_EOL);
            die(self::FAILURE_EXIT);
        }
        
        $this->setStat('original-size', strlen($css));
        
        $cssmin = new Minifier;

        if (!is_null($keepSourceMapComment)) {
            $cssmin->keepSourceMapComment();
        }

        if (!is_null($removeImportantComments)) {
            $cssmin->removeImportantComments();
        }

        if (!is_null($linebreakPosition)) {
            $cssmin->setLineBreakPosition($linebreakPosition);
        }
        
        if (!is_null($memoryLimit)) {
            $cssmin->setMemoryLimit($memoryLimit);
        }

        if (!is_null($backtrackLimit)) {
            $cssmin->setPcreBacktrackLimit($backtrackLimit);
        }

        if (!is_null($recursionLimit)) {
            $cssmin->setPcreRecursionLimit($recursionLimit);
        }
        
        $this->setStat('compression-time-start', microtime(true));
        
        $css = $cssmin->run($css);

        $this->setStat('compression-time-end', microtime(true));
        $this->setStat('peak-memory-usage', memory_get_peak_usage(true));
        $this->setStat('compressed-size', strlen($css));
        
        if (!is_null($dryrun)) {
            $this->showStats();
            die(self::SUCCESS_EXIT);
        }

        if (is_null($output)) {
            fwrite(STDOUT, $css . PHP_EOL);
            $this->showStats();
            die(self::SUCCESS_EXIT);
        }

        if (!is_writable(dirname($output))) {
            fwrite(STDERR, 'Output file is not writable' . PHP_EOL);
            die(self::FAILURE_EXIT);
        }

        if (file_put_contents($output, $css) === false) {
            fwrite(STDERR, 'Compressed CSS code could not be saved to output file' . PHP_EOL);
            die(self::FAILURE_EXIT);
        }

        $this->showStats();

        die(self::SUCCESS_EXIT);
    }

    protected function getOpt($opts, $options)
    {
        $value = null;

        if (is_string($opts)) {
            $opts = array($opts);
        }

        foreach ($opts as $opt) {
            if (array_key_exists($opt, $options)) {
                $value = $options[$opt];
                break;
            }
        }

        return $value;
    }
    
    protected function setStat($statName, $statValue)
    {
        $this->stats[$statName] = $statValue;
    }
    
    protected function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('B', 'K', 'M', 'G', 'T');
        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }
    
    protected function formatMicroSeconds($microSecs, $precision = 2)
    {
        // ms
        $time = round($microSecs * 1000, $precision);
        
        if ($time >= 60 * 1000) {
            $time = round($time / 60 * 1000, $precision) .' m'; // m
        } elseif ($time >= 1000) {
            $time = round($time / 1000, $precision) .' s'; // s
        } else {
            $time .= ' ms';
        }
        
        return $time;
    }
    
    protected function showStats()
    {
        $spaceSavings = round((1 - ($this->stats['compressed-size'] / $this->stats['original-size'])) * 100, 2);
        $compressionRatio = round($this->stats['original-size'] / $this->stats['compressed-size'], 2);
        $compressionTime = $this->formatMicroSeconds(
            $this->stats['compression-time-end'] - $this->stats['compression-time-start']
        );
        $peakMemoryUsage = $this->formatBytes($this->stats['peak-memory-usage']);
        
        print <<<EOT
        
------------------------------
CSSMIN STATS        
------------------------------ 
Space savings:       {$spaceSavings} %       
Compression ratio:   {$compressionRatio}:1
Compression time:    $compressionTime
Peak memory usage:   $peakMemoryUsage


EOT;
    }

    protected function showHelp()
    {
        print <<<'EOT'
Usage: cssmin [options] -i <file> [-o <file>]
  
  -i|--input <file>              File containing uncompressed CSS code.
  -o|--output <file>             File to use to save compressed CSS code.
    
Options:
    
  -h|--help                      Prints this usage information.
  --dry-run                      Performs a dry run displaying statistics.
  --keep-sourcemap[-comment]     Keeps the sourcemap special comment in the output.
  --linebreak-position <pos>     Splits long lines after a specific column in the output.
  --memory-limit <limit>         Sets the memory limit for this script.
  --pcre-backtrack-limit <limit> Sets the PCRE backtrack limit for this script.
  --pcre-recursion-limit <limit> Sets the PCRE recursion limit for this script.
  --remove-important-comments    Removes !important comments from output.

EOT;
    }
}
