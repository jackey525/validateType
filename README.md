# validateType how to use it 

`$a = new validateType();`

`$param = ["type" => "date"];`

`var_dump($a->checkType("1999-1-22",$param));`

`var_dump($a->checkType("1999-01-22",$param));`


# result

`bool(false)`

`bool(true)`
