<?php
namespace app\modules\decision\helpers;
/*
 * Handle Instace For Single word  Stack
 * pashkovdenis@gmail.com 
 *  
 */
class Word { 
 	
	private $root;
	public  $ends ;
	public $full ;  
	public $count ; 
 	public function __construct(){
 		$this->ends = array(); 
 		
 	}	 
	public function addEnd($e)	{
	 	if(!isset($this->ends[$e]))
	 	 $this->ends[$e] = $e; 
	 return $this; 
	}
	
	public function getRoot() {
		return $this->root;
	}
	
	public function  setRoot($root) {
		$this->root = $root; 
		return $this; 
	}
	
	public function  getEnds() {
		return $this->ends ;
	}
	
	public function  __toString()	{
		return $this->root . " "  . join(",",$this->ends);
	}
	
	
	
	
	
	
	
}

?>