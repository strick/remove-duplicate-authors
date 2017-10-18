<?php
namespace Drupal\remove_duplicate_authors\Controller;

use Drupal\Core\DrupalKernel;

class RemoveDuplicateAuthorsController
{
    public function index()
    {
        $this->removeAuthors();
        return array(
            '#markup' => 'Just a test'
        );
        
        
    }
    
    protected function removeAuthors()
    {
        // Get Drupal Connection string.
        $db = \Drupal\Core\Database\Database::getConnection();
        
        // Grab all quotes based by author and group them together
        $query = $db->select('node__field_author', 'nfa');
        $query->fields('nfa', array('entity_id', 'field_author_target_id'));
    	$query->fields('nfq', array('field_quot_value'));
    	$query->fields('fname', array('field_fname_value'));
    	$query->fields('lname', array('field_lname_value'));
        $query->join('node__field_quot', 'nfq', 'nfa.entity_id = nfq.entity_id');
        $query->join('node__field_fname', 'fname', 'nfa.field_author_target_id = fname.entity_id');
        $query->join('node__field_lname', 'lname', 'nfa.field_author_target_id = lname.entity_id');
        $query->orderBy('nfa.field_author_target_id');
        $query->orderBy('nfq.field_quot_value');
        $query->orderBy('nfa.entity_id');
  
        /*
         * select nfa.entity_id, nfq.field_quot_value
            from node__field_author as nfa
            join node__field_quot as nfq
            on nfa.entity_id = nfq.entity_id
            order by nfa.field_author_target_id, nfq.field_quot_value, nfa.entity_id
         */
       
	try{ 
		$quotes = $query->execute()->fetchAll();
	}
	catch(\Exception $e){
		var_dump($e);exit;
}
        
        $nid = 0;
        $prev_author = 0;
        $prev_quote_id = 0;
        $prev_quote = "";
        $prev_author_name = "";
        $duplicates = array();
        $flag = false;
        
        $batch_count = 0;
       
        
        $test_count = 0;
        
	// Look at the ordered list of authors and quotes
        try {
            foreach($quotes as $quote){
                
                // If this quote is the same as the last quote AND it has the same author, mark it as a duplicate.
                if($quote->field_quot_value == $prev_quote && $quote->field_author_target_id == $prev_author){
                    
                    if(!$flag){
                        echo 'Keeping: ' . $prev_quote . ' - <b>' . $prev_author_name . '</b> (AID: ' . $prev_author . ' QID: ' . $prev_quote_id . ')<br />';
                        $flag = true;
                    }
                    $duplicates[] = $quote->entity_id;
                    $batch_count++;
                  
                    echo 'Removing: ' . $quote->field_quot_value . ' - <b>' . $quote->field_fname_value . ' ' . $quote->field_lname_value . '</b> (AID: ' . $quote->field_author_target_id . ' QID: ' . $quote->entity_id . ')<br />';
		           // echo 'Previous one is the: ' . $prev_quote . ' (' . $prev_author . ')<br />';
		            continue;
                }
                
                // If the batch size has gone over 50, then purge the duplicates and continue.
                if($batch_count > 50){
                    
                    $this->_purgeDuplicates($duplicates);
                    
                    $duplicates = array();
                    $batch_count = 0;
                }
 
                // Update the previous author and quote to determine next duplicate.
                $prev_author = $quote->field_author_target_id;
                $prev_quote_id = $quote->entity_id;
                $prev_quote = $quote->field_quot_value;
                $prev_author_name = $quote->field_fname_value . ' ' . $quote->field_lname_value;
                $flag = false;
           
               
                
            }
        }
        catch(\Error $e){

        }


	echo 'There are ' . count($duplicates);
    }
    
    protected function _purgeDuplicates($duplicates)
    {
        try {
            // Delete teh duplicate quotes.
            $storage_handler = \Drupal::entityTypeManager()->getStorage('node');
            $entities = $storage_handler->loadMultiple($duplicates);
            $storage_handler->delete($entities);
        }
        catch(\Exception $e){
            var_dump($e);
            
           var_dump($duplicates);
        }
    }
}
