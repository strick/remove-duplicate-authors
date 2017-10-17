<?php
namespace Drupal\remove_duplicate_authors\Controller;

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;

class RemoveDuplicateAuthorsController
{
    public function index()
    {
        return array(
            '#markup' => 'Just a test'
        );
    }
    
    protected function removeAuthors()
    {
        // Get Drupal Connection string.
        $db = \Drupal\Core\Database\Database::getConnection();
    }
}