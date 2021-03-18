<?php
namespace Algo26\IdnaConvert\NamePrep;

interface NamePrepInterface
{
    /**
     * @param array $inputArray
     *
     * @return array
     */
    public function do(array $inputArray): array;
}
