<?php


class String_Utility
{
    /*
     * Возвращает строку, если она не пустая
     * @var $current_str string - текущая строка
     * @var $str string - строка, которую прибавляет к текущей, если она не пустая
     * @var $flag int - нужна ли запятая перед строкой (1 - да, 0 - нет)
     * return string
     */
    private function insertToString($current_str, $str, $flag = 1)
    {
        if ($str != null)
        {
            if ($flag == 1 && $current_str != null)
            {
                $str = ', '.$str;
            }
            return $current_str . $str;
        }
        return $current_str;
    }
}