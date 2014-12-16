<?php

namespace Pool\LinkmotorBundle\Command;

class ExcelReadFilterLinkbird implements \PHPExcel_Reader_IReadFilter
{
    public function readCell($column, $row, $worksheetName = '')
    {
        if (in_array($column, array('A', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N'))) {
            return true;
        }

        return false;
    }
}
