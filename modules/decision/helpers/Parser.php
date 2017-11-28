<?php

namespace app\modules\decision\helpers;
/*
 * Parse And  Clasigy Words  
 * PashkovDenis@2013 * 
 */
 class Parser { 
	
	private  $source = array();
	private $splitedSource = array();
	private $result = array(); // Single Words 
	private $classes= array(); // Classes of Docuemntws class1= array(), class2 =array() ; 
	private $current_cluster;  // Current Cluster Stack For  comparing.  
	private $cluster_limit =   0 ;  
	private $delimtr   = 2; 
	/**
	 * @param source - List of Strings For Analis
	 */
	public function __construct($source=array())	{
		$this->source = $source ; 
	 	$this->split() ;
	}
	
	/** Split Strings **/
	private function split()	{
	   foreach($this->source as $phrase){ 
	   			$phrase =  $result = preg_replace("/\s+/", " ", $phrase);   	
	   			$phrase = strtolower($phrase);
	   			$words =  preg_split("/ /",$phrase); 
	   		 
				$this->splitedSource[] = $words; 
		}		
		return $this; 	
	}	
	
	
	// set Limit Stack 
	public function setClusterLimit($limit=60){
		$this->cluster_limit = $limit; 
		return $this; 		
	}
	
	
	
	/**
	 * Analyze Strokes And Generete Words  + clasify text 
	 */
	public function parse()	{
 			for($i=0; $i<count($this->splitedSource); $i++){
					$phrase  = ($this->splitedSource[$i]); 
					 
					for($j=0; $j<count(($phrase));$j++){
						$this->compare(($phrase[$j]),$i,$j);
					} 	
			}
			return $this; 
	}	
	
	/*
	 * 
	 * Set How Meny elemnts per gourp 
	 */
	
	public function setDelim($d){
		$this->delimtr = $d; 
		return $this; 
	}
	
	
	
	
	// get lowest DLI 
	public function getminDLI(){
		$tot=0;
		$a= count($this->splitedSource);
            for ($i = 2; $i <= $a; $i++)
            {
                $k=null;
                $k = $a % $i; // k:=a mod i;


                if ($k == 0)
                {
                    $tot=$i;
                    break; // exit;
                }
            }          
		 
		return $tot; 		
	}
	
	/**
	 * Compare Strings  
	 */
	private function compare($key, $fromI, $fromJ)	{
		for($i=$fromI; $i<count($this->splitedSource); $i++){
				$phrase =  $this->splitedSource[$i];  
				for($j=$fromJ; $j<count($phrase);$j++){
					$word =  $phrase[$j] ; 
			 
					$end  = $this->parseEnd($word,$key); 
				 
					if(!$end == "")
						$this->addWord($key,$end,$word); 
					$end = $this->parseEnd($key,$word,$word); 
					if(!$end == "")
						$this->addWord($word,$end,$word);
				}	
			$fromJ = 0 ; 
		}
		return $this; 
	}
	
	/**
 		Try to Find Ending for The Word  
	 */
	private function parseEnd($word, $key)	{
	 
	 
		if($key!=""){
	 	if(strpos($word,$key) !==false  )	{
			return substr($word, mb_strlen($key)); 
		}
		}
		return "";
	}	
	/**
	 * Add NEw Word
	 */
	private function addWord($root, $end, $full="")	{		 
		if($root=="") return  ; 
		foreach($this->result as $w){
			if($w->getRoot() == $root){
				$w->addEnd($end);
			//	$this->reinForceEnds($w); 
				return ; 			
			}
		 }		
		 $w= new Word();   
		 $w->full =  $full ;
		 $w->setRoot($root);  
		 $w->addEnd($end); 
		 $this->reinForceEnds($w); 
		 foreach ($this->source as $s){
		 	$w->count = substr_count($s, $root) ;
		 } 
		  $this->result[] = $w;  
	}
	
	/*
	 * REd ORder Ends By  Count 
	 */
	private function reinForceEnds($word){
		foreach($this->splitedSource as $words){
			foreach($words as $w){
			 	if (strstr($w,$word->getRoot())){
			 		  asort($word->ends); 
			 		  $word->ends = array_unique($word->ends); 
			  	} 
			} 
		} 
	}
	
	/**
	 *
	 * @return list  */
	public function getSource() {
		return $this->source; 
	}
	
	/**
	 Like Constructor  
	 */
	public function  setSource($source=array()) {
	 $this->source = $source ;  
	 $this->split() ;
	}
	
	/**
	 *
	 * @return list of words  
	 */
	public function  getResult() {
		return $this->result; 
	}		 
	// Compare  2 Words Stack 
	private  function  check($terms_in_article1, $terms_in_article2) {
		    $length1 = count($terms_in_article1); 
		    $length2 = count($terms_in_article2); 
		    $all_terms = array_merge($terms_in_article1, $terms_in_article2);
		    $all_terms = array_unique($all_terms);
		    foreach ($all_terms as $all_termsa) {
		        $term_vector1[$all_termsa] = 0;
		        $term_vector2[$all_termsa] = 0;
		    }
		    foreach ($terms_in_article1 as $terms_in_article1a) {
		        $term_vector1[$terms_in_article1a]++;
		    }
		    foreach ($terms_in_article2 as $terms_in_article2a) {
		        $term_vector2[$terms_in_article2a]++;
		    }
		    $score = 0;
		    foreach ($all_terms as $all_termsa) {
		        $score += $term_vector1[$all_termsa]*$term_vector2[$all_termsa];
		    }
		    $score = $score/($length1*$length2);
		    $score *= 500;  
		    return $score;
	
	}
	// Replace Words With Roots Only  
	private function stripRoot($words){
		  foreach($words as $k=>$w){
				if($w=="") continue ; 
				foreach($this->result as $word){
				  	if(strstr($w,$word->getRoot())){
						$words[$k] = $word->getRoot(); 
					} 
				} 
			}	 
	 
		return $words ; 
	}
	
	
	// reordering by match array  
	private function  calculateLimit(){
		$reorder = array(); 
	  
		 $reversed = array_reverse($this->splitedSource); 
		 $summa = array();  
		  
		 
		 $data = array(); 
		 foreach ($this->splitedSource as $va)
		 	$data[]  = join(" ", $va);
		 
		 $groups = array();
		 $threshold = 20;
		 
		 foreach ($data as $index => $string) {
		 	$groupScores = array();
		 
		 	// Check item against all grouped items
		 	foreach ($groups as $groupKey => $group) {
		 		$highestScore = 0;
		 		foreach ($group as $item) {
		 			similar_text($string, $item, $score);
		 			if ($score < $threshold) {
		 				// Doesn't match an item in the group, so move to next  group
		 				continue 2;
		 			}
		 			if ($score > $highestScore) {
		 				$highestScore = $score;
		 			}
		 		}
		 
		 		$groupScores[$groupKey] = $highestScore;
		 	}
		 
		 	if (!empty($groupScores)) {
		 		// Insert into the group with the highest score
		 		arsort($groupScores);
		 		$groups[key($groupScores)][] = $string;
		 	} else {
		 		// Not matched. Put into new group
		 		$groups[] = array($string);
		 	}
		 }
		 
 
		 foreach ($groups as $g){
		 		foreach($g as $string) 
		 				$summa[]  = preg_split("/\s+/", $string);	
		 	
		 }
		  
	 
		 echo "<hr> ";
		$this->splitedSource  = $summa;
		  
	}
	
	// Classification   Methods    	
	public function classify(){
		$clusterst   = array(); 
		$cluster  = new Classed(); 
	 	$clusterst[] = $cluster ; 
	 	if($this->cluster_limit ==0 )
			$this->calculateLimit();  
	 	$summ = 0 ; 
 				// How to auto detrminate limit of 
			foreach($this->splitedSource as $k=>$words){
 			 
 			    	 $proc =   $this->check($cluster->summa(), $this->stripRoot($words)); 
 			  		
 			    	if(count($cluster->summa())>1){
  
				 	  	 	 if($proc>=$this->cluster_limit){
				 	  	 	 $cluster = new Classed(); 	
				 	  	 	 $cluster->proc = $proc ; 	 	  	 	
				 	  	 	 $clusterst[]= $cluster ;	
				 	  	 	 $summ+= $proc ; 
				 	  	 
				 	  	 	 $this->cluster_limit = $proc/$this->delimtr    ;	 	  	 	 
				 	  	 	 }
 			    	}
		 	  	 	 
		 	  	 	 $cluster->addDoc($words);
		 	  	 	
			}	
			return $clusterst ; 	
	}
	
 
}
/*
 * Private Class  That Used as Container For  
 * Classification Results. 
 * pashkovdenis@gmail.com * 
 * RommieVector@2013
 */
final class Classed{
	
	private $docs = array(); 
	private $summa ; 
	private $name  ; 
	public $proc ; 
	public function __construct(){
		$this->summa =  "" ; 
		$this->name =  ""; 
	}
	// Get Summ of docs For Future Comparing  
	public function summa(){
		return preg_split("/ /",$this->summa); 	
	}	 
	public function setname($n=""){
		$this->name = $n ; 
	}
	// Add Doc to Stack 
	public function addDoc($doc){
		$this->docs[] = join(" ",$doc) ; 
		$this->summa  =  join(" ", $this->docs); 		
	}
		
	public function getDocs(){
		return $this->docs; 
	}
	
	public function __toString(){
 			
		return  join("<hr>", $this->docs); 
	}
	
} 