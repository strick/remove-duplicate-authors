<?php
namespace Drupal\remove_duplicate_authors\Controller;

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;

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
        $query->fields('nfa', array('nfa.entity_id', 'nfa.field_author_target_id', 'nfq.field_quot_value'));
        $query->join('node__field_quot', 'nfq', 'nfa.entity_id = nfq.entity_id');
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
        
        $quotes = $query->execute()->fetchAll();
        
        $nid = 0;
        $prev_author = 0;
        $prev_quote = "";
        $duplicates = array();
       
        
        $test_count = 0;
        
        // Look at the ordered list of authors and quotes
        try {
            foreach($quotes as $quote){
                
                // If this quote is the same as the last quote AND it has the same author, mark it as a duplicate.
                if($quote->field_quot_value == $prev_quote && $quote->field_author_target_id == $prev_author){
                    $duplicates[] = $quote->entity_id;
                    echo 'Found duplicate for: ' . $quote->field_quot_value . ' (' . $quote->entity_id . ')<br />';
                    $test_count++;
                }
                
                // Update the previous author and quote to determine next duplicate.
                $prev_author = $quote->field_author_target_id;
                $prev_quote = $quote->field_quot_value;
           
                
                if($test_count > 100) exit;
            }
        }
        catch(\Error $e){

        }
        // 
        /*
        // Delete teh duplicate quotes.
        $storage_handler = \Drupal::entityTypeManager()->getStorage('node');
        $entities = $storage_handler->loadMultiple($duplicates);
        $storage_handler->delete($entities);
        */
    }
}