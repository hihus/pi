<?php
/**
 * @date 2010/07/13 17:00:00
 * @version $Revision$ 
 * @brief 
 *  
 **/

class EXQueue
{ 
  private 
    $arrQueue,       // Array of queue items 
    $intBegin,       // Begin of queue - head 
    $intEnd;         // End of queue - tail 


  public function __construct() 
  { 
    $this->arrQueue     = Array(); 
    $this->clear(); 
  }    

  function push( $objQueueItem  ) 
  {     
    $this->arrQueue[ $this->intEnd ] = $objQueueItem;    
    $this->intEnd++;  
  } 

  /**
   * See element only
   *
   * @return mixed
   */
  function top()  
  {
      if($this->isEmpty())return false;     
      return $this->arrQueue[$this->intBegin]; 
  }

  /**
   * Pop only
   */
  function pop() 
  { 
      if($this->isEmpty())return;
      else $this->intBegin++; 
  } 

  function isEmpty() 
  { 
    return ($this->intEnd <= $this->intBegin); 
  } 

  function clear() 
  { 
    $this->intBegin       = 0; 
    $this->intEnd         = 0; 
  } 
}
