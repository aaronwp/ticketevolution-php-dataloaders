<?php

/**
 * LICENSE
 *
 * This source file is subject to the new BSD (3-Clause) License that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://choosealicense.com/licenses/bsd-3-clause/
 *
 * @category    DataLoader
 * @package     DataLoader\DataLoader
 * @copyright   Copyright (c) 2013 J Cobb. (http://jcobb.org)
 * @license     http://choosealicense.com/licenses/bsd-3-clause/ BSD (3-Clause) License
 */


namespace DataLoader\DataLoader\Events;
use DataLoader\DataLoader\AbstractDataLoader;


/**
 * DataLoader for a specific API endpoint to cache the data into local table(s)
 *
 * @category    DataLoader
 * @package     DataLoader\DataLoader
 * @copyright   Copyright (c) 2013 J Cobb. (http://jcobb.org)
 * @license     http://choosealicense.com/licenses/bsd-3-clause/ BSD (3-Clause) License
 */
class Deleted extends AbstractDataLoader
{
    /**
     * Which endpoint we are hitting. This is used in the `dataLoaderStatus` table
     *
     * @var string
     */
    var $endpoint = 'events';


    /**
     * The type of items to get [active|deleted]
     *
     * @var string
     */
    var $endpointState = 'deleted';


    /**
     * The \TicketEvolution\Webservice method to use for the API request
     *
     * @var string
     */
    protected $_webServiceMethod = 'listEventsDeleted';


    /**
     * The class of the table
     *
     * @var Zend_Db_Table
     */
    protected $_tableClass = '\DataLoader\Db\Table\Events';


    /**
     * Manipulates the $result data into an array to be passed to the table row
     *
     * @param object $result    The current result item
     * @return void
     */
    protected function _formatData($result)
    {
        $this->_data = array(
            'eventId'           => (int)    $result->id,
            'merged_into'       =>          $result->merged_into,
            'deleted_at'        => (string) $result->deleted_at,
            'eventsStatus'      => (int)    0,
        );
    }


    /**
     * Allows pre-save logic to be applied.
     * Subclasses may override this method.
     *
     * @param object $result    The current result item. Only passed to enable progress output
     * @return void
     */
    protected function _preSave($result)
    {
    }


    /**
     * Allows post-save logic to be applied.
     * Subclasses may override this method.
     *
     * @param object $result    The current result item
     * @return void
     */
    protected function _postSave($result)
    {
        /**
         * The easiest way to set the tevoPerformers for this event to inactive
         * is to delete() this event and let the operation cascade to the
         * tevoEventPerformers.
         *
         * So, although it is kind of redundant to delete() since we just save()ed
         * as inactive we'll still do it since delete() does not allow us to
         * set properties such as 'deleted_at' at the same time.
         *
         * NOTE: delete() is overridden in DataLoader\Db\Table\AbstractTable to
         * only toggle the status to inactive, but it still cascades doing the same.
         */
        try {
            $this->_tableRow->delete();

            if ($this->_showProgress) {
                echo '<p class="error">'
                   . 'Successful delete() of <strong>' . $result->id . '</strong>: in `tevoEvents` and the related `tevoEventPerformers`'
                   . '</p>' . PHP_EOL;
            }
        } catch (\Exception $e) {
            if ($this->_showProgress) {
                echo '<p>'
                   . 'Error attempting to delete() <strong>' . $result->id . '</strong>: in `tevoEvents` and the related `tevoEventPerformers`'
                   . '</p>' . PHP_EOL;
            }

            throw new namespace\Exception($e);
        }
    }


}
