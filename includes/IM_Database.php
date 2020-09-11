<?php
namespace Hellodev\InventoryManager;

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Abstract class which has helper functions to get data from the database
 * Based on: https://gist.github.com/paulund/6687336
 */
abstract class IM_Database
{
    /**
     * The current table name
     *
     * @var boolean
     */
    protected $tableName = false;

    /**
     * Constructor for the database class to inject the table name
     *
     * @param String $tableName - The current table name
     */
    public function __construct() { }

    /**
     * Insert data into the current data
     *
     * @param  array  $data - Data to enter into the database table
     *
     * @return InsertQuery Object
     */
    public function insert(array $data)
    {
        global $wpdb;

        if(empty($data))
        {
            return false;
        }

        $wpdb->insert($this->tableName, $data);

        return $wpdb->insert_id;
    }

    /**
     * Get all from the selected table
     *
     * @param  String $orderBy - Order by column name
     *
     * @return Table result
     */
    public function get_all( $orderBy = NULL )
    {
        global $wpdb;

        $sql = 'SELECT * FROM `'.$this->tableName.'`';

        if(!empty($orderBy))
        {
            $sql .= ' ORDER BY ' . $orderBy;
        }

        $all = $wpdb->get_results($sql);

        return $all;
    }

    /**
     * Get a value by a condition
     *
     * @param  Array $conditionValue - A key value pair of the conditions you want to search on
     * @param  String $condition - A string value for the condition of the query default to equals
     *
     * @return Table result
     */
    public function get_by(array $conditionValue, $condition = '=', $orderBy = NULL)
    {
        global $wpdb;

        $sql = 'SELECT * FROM `'.$this->tableName.'` WHERE ';

        $i = 0;
        foreach ($conditionValue as $field => $value) {
            switch(strtolower($condition))
            {
                case 'in':
                    if(!is_array($value))
                    {
                        throw new \Exception("Values for IN query must be an array.", 1);
                    }

                    $sql .= $wpdb->prepare('`%s` IN (%s)', $field, implode(',', $value));
                break;

                default:
                    if($i > 0) {
                      $sql .= " AND ";
                    }
                    $sql .= $wpdb->prepare('`'.$field.'` '.$condition.' %s', $value);
                break;
            }
            $i++;
        }

        if(!empty($orderBy))
        {
            $sql .= ' ORDER BY ' . $orderBy;
        }

        $result = $wpdb->get_results($sql);

        return $result;
    }

    /**
     * Update a table record in the database
     *
     * @param  array  $data           - Array of data to be updated
     * @param  array  $conditionValue - Key value pair for the where clause of the query
     *
     * @return Updated object
     */
    public function update(array $data, array $conditionValue)
    {
        global $wpdb;

        if(empty($data))
        {
            return false;
        }

        $updated = $wpdb->update( $this->tableName, $data, $conditionValue);

        return $updated;
    }

    /**
     * Delete row on the database table
     *
     * @param  array  $conditionValue - Key value pair for the where clause of the query
     *
     * @return Int - Num rows deleted
     */
    public function delete(array $conditionValue)
    {
        global $wpdb;

        $deleted = $wpdb->delete( $this->tableName, $conditionValue );

        return $deleted;
    }

	/**
	 * [count counts the rows of a specific table]
	 * @return [int] [count]
	 */
	public function count() {
		global $wpdb;

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM $this->tableName" );

		return $count;
	}
}
