<?php
namespace Algo26\IdnaConvert;

use Algo26\IdnaConvert\Punycode\FromPunycode;
use Algo26\IdnaConvert\TranscodeUnicode\TranscodeUnicode;

class ToUnicode extends AbstractIdnaConvert implements IdnaConvertInterface
{
    /** @var TranscodeUnicode */
    private $unicodeTransCoder;

    /** @var FromPunycode */
    private $punycodeEncoder;

    public function __construct()
    {
        $this->unicodeTransCoder = new TranscodeUnicode();
        $this->punycodeEncoder = new FromPunycode();
    }

    public function convert(string $host): string
    {
        // Drop any whitespace around
        $input = trim($host);

        $hostLabels = explode('.', $input);
        foreach ($hostLabels as $index => $label) {
            $return = $this->punycodeEncoder->convert($label);
            if (!$return) {
                $return = $label;
            }
            $hostLabels[$index] = $return;
        }

        return implode('.', $hostLabels);
    }
}
