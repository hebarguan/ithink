<?php
 
class my 
{
    function i()
    {
         echo "i";
    }
}

class name extends my
{
    function am()
    {
        echo "am";
     }
}

class ais extends name
{
    function an()
    {
        echo "hebar";
    }
}

$test = new ais();
$test->i();