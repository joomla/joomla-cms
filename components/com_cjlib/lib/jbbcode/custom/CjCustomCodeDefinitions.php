<?php 
namespace JBBCode;

require_once 'YouTubeCodeDefinition.php';
require_once 'ImageCodeDefinition.php';
require_once 'ULCodeDefinition.php';
require_once 'OLCodeDefinition.php';
require_once 'TableCodeDefinition.php';

class CjCodeDefinitionSet implements CodeDefinitionSet
{
	protected $definitions = array();
	
	public function __construct()
	{
		$youtube = new \YoutubeCodeDefinition();
		array_push($this->definitions, $youtube);
		
		$image = new \ImageCodeDefinition();
		array_push($this->definitions, $image);

		$ul = new \ULCodeDefinition();
		array_push($this->definitions, $ul);

		$ol = new \OLCodeDefinition();
		array_push($this->definitions, $ol);

		$table = new \TableCodeDefinition();
		array_push($this->definitions, $table);
		
		$builder = new CodeDefinitionBuilder('s', '<span style="text-decoration: line-through">{param}</span>');
		array_push($this->definitions, $builder->build());
		
		$builder = new CodeDefinitionBuilder('sup', '<sup>{param}</sup>');
		array_push($this->definitions, $builder->build());
		
		$builder = new CodeDefinitionBuilder('sub', '<sub>{param}</sub>');
		array_push($this->definitions, $builder->build());
		
		$builder = new CodeDefinitionBuilder('code', '<pre>{param}</pre>');
		array_push($this->definitions, $builder->build());

		$builder = new CodeDefinitionBuilder('left', '<div class="text-left">{param}</div>');
		array_push($this->definitions, $builder->build());

		$builder = new CodeDefinitionBuilder('right', '<div class="text-right">{param}</div>');
		array_push($this->definitions, $builder->build());

		$builder = new CodeDefinitionBuilder('center', '<div class="text-center">{param}</div>');
		array_push($this->definitions, $builder->build());

		$builder = new \JBBCode\CodeDefinitionBuilder('quote', '<blockquote>{param}</blockquote>');
		$builder->setUseOption(true);
		array_push($this->definitions, $builder->build());

		$builder = new CodeDefinitionBuilder('size', '<span class="cj-font-size-{option}">{param}</span>');
		$builder->setUseOption(true)->setParseContent(true);
		array_push($this->definitions, $builder->build());
		
        $builder = new CodeDefinitionBuilder('attachment', '{CJATTACHMENT ["id": {option}]}');
        $builder->setUseOption(true)->setParseContent(true);
        array_push($this->definitions, $builder->build());
        
//         $builder = new \JBBCode\CodeDefinitionBuilder('font', '<span style="font-family: {option}">{param}</span>');
//         $builder->setUseOption(true);
//         $builder->setOptionValidator($cssValidator);
//         array_push($this->definitions, $builder->build());
	}
	
	public function getCodeDefinitions()
	{
		return $this->definitions;
	}
}