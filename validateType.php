<?php
class validateType {
    
    private static $_mbstringAvailable;     // 取得 validate data
    
    public function checkType($value, $params)
    {
        $type=$params['type']==='float' ? 'double' : $params['type'];

        $defaultArr = array(
            'strict'=>false,
            'dateFormat'=>'yyyy-MM-dd',
            'datetimeFormat'=>'yyyy-MM-dd hh:mm',
            'timeFormat'=>'hh:mm',
        );
        $paramArr = array_merge($defaultArr, $params);

         if ($type !== gettype($value) &&
             ($paramArr['strict'] || is_array($value) || is_object($value) ||
                 is_resource($value) || is_bool($value))) {
             return false;
         }

        if ($type === 'integer') {
            if (!preg_match('/^[-+]?[0-9]+$/', trim($value))) {
                return false;
            }
        } elseif ($type==='double') {
            if (!preg_match('/^[-+]?([0-9]*.)?[0-9]+([eE][-+]?[0-9]+)?$/', trim($value))) {
                return false;
            }
        } elseif ($type==='date') {
            if (self::parse(
                    $value,
                    $paramArr['dateFormat'],
                    array('month'=>1,'day'=>1,'hour'=>0,'minute'=>0,'second'=>0)
                )===false) {
                return false;
            }
        } elseif ($type==='time') {
            if (self::parse($value, $paramArr['timeFormat'])===false) {
                return false;
            }
        } elseif ($type==='datetime') {
            if (self::parse(
                    $value,
                    $paramArr['datetimeFormat'],
                    array('month'=>1,'day'=>1,'hour'=>0,'minute'=>0,'second'=>0)
                )===false) {
                return false;
            }
        }

        return true;
    }
  
  
  public static function parse($value,$pattern='MM/dd/yyyy',$defaults=array())
    {
         if(self::$_mbstringAvailable===null)
             self::$_mbstringAvailable=extension_loaded('mbstring');

        $tokens=self::tokenize($pattern);

        $i=0;
        $n=self::$_mbstringAvailable ? mb_strlen($value,"utf-8") : strlen($value);

        foreach($tokens as $token)
        {
            switch($token)
            {
                case 'yyyy':
                {
                    if(($year=self::parseInteger($value,$i,4,4))===false)
                        return false;
                    $i+=4;
                    break;
                }
                case 'yy':
                {
                    if(($year=self::parseInteger($value,$i,1,2))===false)
                        return false;
                    $i+=strlen($year);
                    break;
                }
                case 'MMMM':
                {
                    $monthName='';
                    if(($month=self::parseMonth($value,$i,'wide',$monthName))===false)
                        return false;
                    $i+=self::$_mbstringAvailable ? mb_strlen($monthName,"utf-8") : strlen($monthName);
                    break;
                }
                case 'MMM':
                {
                    $monthName='';
                    if(($month=self::parseMonth($value,$i,'abbreviated',$monthName))===false)
                        return false;
                    $i+=self::$_mbstringAvailable ? mb_strlen($monthName,"utf-8") : strlen($monthName);
                    break;
                }
                case 'MM':
                {
                    if(($month=self::parseInteger($value,$i,2,2))===false)
                        return false;
                    $i+=2;
                    break;
                }
                case 'M':
                {
                    if(($month=self::parseInteger($value,$i,1,2))===false)
                        return false;
                    $i+=strlen($month);
                    break;
                }
                case 'dd':
                {
                    if(($day=self::parseInteger($value,$i,2,2))===false)
                        return false;
                    $i+=2;
                    break;
                }
                case 'd':
                {
                    if(($day=self::parseInteger($value,$i,1,2))===false)
                        return false;
                    $i+=strlen($day);
                    break;
                }
                case 'h':
                case 'H':
                {
                    if(($hour=self::parseInteger($value,$i,1,2))===false)
                        return false;
                    $i+=strlen($hour);
                    break;
                }
                case 'hh':
                case 'HH':
                {
                    if(($hour=self::parseInteger($value,$i,2,2))===false)
                        return false;
                    $i+=2;
                    break;
                }
                case 'm':
                {
                    if(($minute=self::parseInteger($value,$i,1,2))===false)
                        return false;
                    $i+=strlen($minute);
                    break;
                }
                case 'mm':
                {
                    if(($minute=self::parseInteger($value,$i,2,2))===false)
                        return false;
                    $i+=2;
                    break;
                }
                case 's':
                {
                    if(($second=self::parseInteger($value,$i,1,2))===false)
                        return false;
                    $i+=strlen($second);
                    break;
                }
                case 'ss':
                {
                    if(($second=self::parseInteger($value,$i,2,2))===false)
                        return false;
                    $i+=2;
                    break;
                }
                case 'a':
                {
                    if(($ampm=self::parseAmPm($value,$i))===false)
                        return false;
                    if(isset($hour))
                    {
                        if($hour==12 && $ampm==='am')
                            $hour=0;
                        elseif($hour<12 && $ampm==='pm')
                            $hour+=12;
                    }
                    $i+=2;
                    break;
                }
                default:
                {
                    $tn=self::$_mbstringAvailable ? mb_strlen($token,"utf-8") : strlen($token);
                    if($i>=$n || ($token{0}!='?' && (self::$_mbstringAvailable ? mb_substr($value,$i,$tn,"utf-8") : substr($value,$i,$tn))!==$token))
                        return false;
                    $i+=$tn;
                    break;
                }
            }
        }
        
        if($i<$n)
            return false;

        if(!isset($year))
            $year=isset($defaults['year']) ? $defaults['year'] : date('Y');
        if(!isset($month))
            $month=isset($defaults['month']) ? $defaults['month'] : date('n');
        if(!isset($day))
            $day=isset($defaults['day']) ? $defaults['day'] : date('j');

        if(strlen($year)===2)
        {
            if($year>=70)
                $year+=1900;
            else
                $year+=2000;
        }
        $year=(int)$year;
        $month=(int)$month;
        $day=(int)$day;

        if(
            !isset($hour) && !isset($minute) && !isset($second)
            && !isset($defaults['hour']) && !isset($defaults['minute']) && !isset($defaults['second'])
        )
            $hour=$minute=$second=0;
        else
        {
            if(!isset($hour))
                $hour=isset($defaults['hour']) ? $defaults['hour'] : date('H');
            if(!isset($minute))
                $minute=isset($defaults['minute']) ? $defaults['minute'] : date('i');
            if(!isset($second))
                $second=isset($defaults['second']) ? $defaults['second'] : date('s');
            $hour=(int)$hour;
            $minute=(int)$minute;
            $second=(int)$second;
        }

        if(self::isValidDate($year,$month,$day) && self::isValidTime($hour,$minute,$second))
            return self::getTimestamp($hour,$minute,$second,$month,$day,$year);
        else
            return false;
    }

    /*
	 * @param string $pattern the pattern that the date string is following
	 */
    private static function tokenize($pattern)
    {
        if(!($n=self::$_mbstringAvailable ? mb_strlen($pattern,"utf-8") : strlen($pattern)))
            return array();
        $tokens=array();
        $c0=self::$_mbstringAvailable ? mb_substr($pattern,0,1,"utf-8") : substr($pattern,0,1);

        for($start=0,$i=1;$i<$n;++$i)
        {
            $c=self::$_mbstringAvailable ? mb_substr($pattern,$i,1,"utf-8") : substr($pattern,$i,1);
            if($c!==$c0)
            {
                $tokens[]=self::$_mbstringAvailable ? mb_substr($pattern,$start,$i-$start,"utf-8") : substr($pattern,$start,$i-$start);
                $c0=$c;
                $start=$i;
            }
        }
        $tokens[]=self::$_mbstringAvailable ? mb_substr($pattern,$start,$n-$start,"utf-8") : substr($pattern,$start,$n-$start);
        return $tokens;
    }

    /**
     * @param string $value the date string to be parsed
     * @param integer $offset starting offset
     * @param integer $minLength minimum length
     * @param integer $maxLength maximum length
     * @return string parsed integer value
     */
    protected static function parseInteger($value,$offset,$minLength,$maxLength)
    {
        for($len=$maxLength;$len>=$minLength;--$len)
        {
            $v=self::$_mbstringAvailable ? mb_substr($value,$offset,$len,"utf-8") : substr($value,$offset,$len);
            if(ctype_digit($v) && (self::$_mbstringAvailable ? mb_strlen($v,"utf-8") : strlen($v))>=$minLength)
                return $v;
        }
        return false;
    }

    /**
     * @param string $value the date string to be parsed
     * @param integer $offset starting offset
     * @return string parsed day period value
     */
    protected static function parseAmPm($value, $offset)
    {
        $v=strtolower(self::$_mbstringAvailable ? mb_substr($value,$offset,2,"utf-8") : substr($value,$offset,2));
        return $v==='am' || $v==='pm' ? $v : false;
    }

    /**
    * @param string $value the date string to be parsed.
    * @param integer $offset starting offset.
    * @param string $width month name width. It can be 'wide', 'abbreviated' or 'narrow'.
    * @param string $monthName extracted month name. Passed by reference.
    * @return string parsed month name.
    * @since 1.1.13
    */
    protected static function parseMonth($value,$offset,$width,&$monthName)
    {
        $valueLength=self::$_mbstringAvailable ? mb_strlen($value,"utf-8") : strlen($value);
        for($len=1; $offset+$len<=$valueLength; $len++)
        {
            $monthName=self::$_mbstringAvailable ? mb_substr($value,$offset,$len,"utf-8") : substr($value,$offset,$len);
            if(!preg_match('/^[p{L}p{M}]+$/u',$monthName)) // unicode aware replacement for ctype_alpha($monthName)
            {
                $monthName=self::$_mbstringAvailable ? mb_substr($monthName,0,-1,"utf-8") : substr($monthName,0,-1);
                break;
            }
        }
        $monthName=self::$_mbstringAvailable ? mb_strtolower($monthName,"utf-8") : strtolower($monthName);

        // set the default timezone to use. Available since PHP 5.1
        date_default_timezone_set('UTC');

        if($width == 'wide')
            $monthNames=date("F", strtotime($value));
        else
            $monthNames=date("M", strtotime($value));
        foreach($monthNames as $k=>$v)
            $monthNames[$k]=rtrim(self::$_mbstringAvailable ? mb_strtolower($v,"utf-8") : strtolower($v),'.');

        $monthNamesStandAlone=$monthNames=date("n", strtotime($value));
        foreach($monthNamesStandAlone as $k=>$v)
            $monthNamesStandAlone[$k]=rtrim(self::$_mbstringAvailable ? mb_strtolower($v,"utf-8") : strtolower($v),'.');

        if(($v=array_search($monthName,$monthNames))===false && ($v=array_search($monthName,$monthNamesStandAlone))===false)
            return false;
        return $v;
    }

    /**
     * Checks to see if the year, month, day are valid combination.
     * @param integer $y year
     * @param integer $m month
     * @param integer $d day
     * @return boolean true if valid date, semantic check only.
     */
    public static function isValidDate($y,$m,$d)
    {
        return checkdate($m, $d, $y);
    }
    /**
     * Checks to see if the hour, minute and second are valid.
     * @param integer $h hour
     * @param integer $m minute
     * @param integer $s second
     * @param boolean $hs24 whether the hours should be 0 through 23 (default) or 1 through 12.
     * @return boolean true if valid date, semantic check only.
     */
    public static function isValidTime($h,$m,$s,$hs24=true)
    {
        if($hs24 && ($h < 0 || $h > 23) || !$hs24 && ($h < 1 || $h > 12)) return false;
        if($m > 59 || $m < 0) return false;
        if($s > 59 || $s < 0) return false;
        return true;
    }

    /**
     * Generates a timestamp.
     * This is the same as the PHP function {@link mktime http://php.net/manual/en/function.mktime.php}.
     * @param integer $hr hour
     * @param integer $min minute
     * @param integer $sec second
     * @param integer|boolean $mon month
     * @param integer|boolean $day day
     * @param integer|boolean $year year
     * @param boolean $is_gmt whether this is GMT time. If true, gmmktime() will be used.
     * @return integer|float a timestamp given a local time.
     */
    public static function getTimestamp($hr,$min,$sec,$mon=false,$day=false,$year=false,$is_gmt=false)
    {
        if ($mon === false)
            return $is_gmt? @gmmktime($hr,$min,$sec): @mktime($hr,$min,$sec);
        return $is_gmt ? @gmmktime($hr,$min,$sec,$mon,$day,$year) : @mktime($hr,$min,$sec,$mon,$day,$year);
    }
  
}
