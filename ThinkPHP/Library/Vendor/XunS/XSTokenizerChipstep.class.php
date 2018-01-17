<?php
class XSTokenizerChipstep implements XSTokenizer
{
	private $arg = 2;
	public function __construct($arg = null)
	{
		if ($arg !== null && $arg !== '') {
			$this->arg = intval($arg);
			if ($this->arg < 1 || $this->arg > 255) {
				throw new XSException('Invalid argument for ' . __CLASS__ . ': ' . $arg);
			}
		}
	}
	public function getTokens($value, XSDocument $doc = null)
	{
		$terms = array();
		if(strpos($value,'€€') !==  false){
			$value_arr = explode('€€',$value);
			foreach($value_arr as $v){
				$terms[] = $this->getTokens($v);
			}
			return $terms;
		}
		$i = $this->arg;
		$len = mb_strlen($value,'utf-8');
		for($start = 0;$start < $len;$start ++){
			while(true){
				$tmp = mb_substr($value, $start, $i,'utf-8');
				if(mb_strlen($tmp,'utf-8') >= 2){
					$terms[] = $start == 0 ? array($tmp) : $tmp;
					//$terms[] = $tmp;
				}
				if ($i >= ($len - $start)) {
					break;
				}
				$i += $this->arg;
			}
			$i = $this->arg;
		}

		return $terms;
	}
}