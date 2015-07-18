<?php

require_once('Term.php') ;

/**
 * This class represents a particular week as part of a Term object.  An instance
 * can be created by calling the 'getWeek' method of the Term class for a given
 * term, rather than calling this class' constructor directly
 * 
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * @author Damien Clark <damo.clarky@gmail.com>
 * @author Colin Beer <colinwbeer@gmail.com>
 */
class TermWeek
{
	/**
	 * @var integer The week as an epoch week representing this termWeek instance
	 */
	protected $weekEpoch = null ;

	/**
	 * @var string The week code representing this week
	 */
	protected $weekCode = null ;
	
	/**
	 * @var Term An instance of the Term class that this week belongs to
	 */
	protected $term = null ;
	
	/**
	 * @var array Data structure holding term information for all terms
	 */
	protected $termData = null ;

	/**
	 * @var string Short description of the week (eg. '1')
	 */
	protected $shortName = null ;
	
	/**
	 * @var string The description of the week (eg. 'Week 1')
	 */
	protected $name = null ;
	
	
	/**
	 * Constructor for TermWeek class
	 * 
	 * @param array $termData  An array structure derived from the createSerializedData() method of Term class
	 * @param Term $term      The term this TermWeek belongs to
	 * @param string|integer $week  The week expressed as either a week code or a week epoch
	 * @param string $shortname The shortname description of this week (eg. '1')
	 * @param string $name      The description of this week (eg. 'Week 1')
	 * 
	 * @return TermWeek    Returns an instance of this week
	 */
	public function __construct($termData,$term,$week,$shortname,$name)
	{
		$this->termData = $termData ;
		$this->term = $term ;
		$this->shortName = $shortname ;
		$this->name = $name ;

		$strm = $term->getStrm() ;
		if(is_numeric($week))
		{
			$this->weekEpoch = $week ;
			//@todo need to test whether this key exists otherwise barf as the weekEpoch is invalid - use array_keys_exist
			$this->weekCode = $termData['strms'][$strm]['weeks'][$week] ;
		}
		elseif(is_string($week))
		{
			$this->weekCode = $week ;
			//@todo need to test whether this key exists otherwise barf as the weekcode is invalid - use array_keys_exist
			$flipped = array_flip($termData['strms'][$strm]['weeks']) ;
			//@todo need to test whether this key exists otherwise barf as the weekcode is invalid - use array_keys_exist
			$this->weekEpoch = $flipped[$week] ;
		}
		else
		  throw new TermException("Invalid \$week provided: $week",TermException::INPUT_ERROR) ;
		
	}
	
	/**
	 * This method will return a short name description of the week (eg. '1') 
	 * 
	 * @return string    Short description of the week
	 */
	public function getWeekShortname()
	{
		return $this->shortName ;
	}
	
	/**
	 * This method will return the long name of the week (eg. 'Week 1')
	 * 
	 * @return string    Name of the week
	 */
	public function getWeekName()
	{
		return $this->name ;	
	}
	
	/**
	 * This method will return the unix epoch for the start of the week
	 * 
	 * @return integer    unix epoch as an integer
	 */
	public function getWeekStartAsUnixEpoch()
	{
		return Term::epochWeekToUnixEpoch($this->weekEpoch) ;
	}
	
	/**
	 * This method returns the unix epoch for the end of the current week
	 * 
	 * @return integer    Unix epoch
	 */
	public function getWeekEndAsUnixEpoch()
	{
		return Term::epochWeekToUnixEpoch($this->weekEpoch+1)-1 ;
	}
	
	/**
	 * This method returns a date time object representing the week's end
	 * 
	 * @return object    date time object
	 */
	public function getWeekEnd()
	{
		$date=$this->getWeekStart();
		//Add on 6 days 23 hrs, 59 mins & 59 secs
		$date->add(new DateInterval('P6DT23H59M59S'));
		return $date ;
	}
	
	/**
	 * This method returns a datetime object for this week epoch
	 * 
	 * @return object    date time object
	 */
	public function getWeekStart()
	{
		return Term::epochWeekToDate($this->weekEpoch) ;
	}
	
	/**
	 * This method returns this week's week epoch
	 * 
	 * @return integer    Week epoch
	 */
	public function getWeekEpoch()
	{
		return $this->weekEpoch;
	}
	
	/**
	 * This method returns the week code for this instance
	 * 
	 * @return string    week code (eg. w1)
	 */
	public function getWeekCode()
	{
		return $this->weekCode ;
	}
	
	/**
	 * This method returns true if this week instance is the current week of term
	 * otherwise false
	 * 
	 * @return boolean    True if this week object is the current week of term otherwise false
	 */
	public function isCurrentWeek()
	{
		//It is the current week if the week epoch for this object is equal to
		//the week epoch of the present DateTime
		return ($this->getWeekEpoch() == Term::calculateWeekEpoch(new DateTime())) ;
	}
	
	/**
	 * This method will return true if $this week is greater than the given $week
	 * 
	 * @param TermWeek|string|integer $week The week to test is before $this week as either a week constant, an epoch week or a TermWeek object
	 * 
	 * @return boolean    Returns true if $week is before $this, otherwise false
	 */
	public function greaterThan($week)
	{
		if(is_string($week) or is_numeric($week))
			$week = $this->term->getWeek($week) ;
		elseif(!$week instanceof TermWeek)
			throw new TermException("Invalid \$week provided",TermException::INPUT_ERROR) ;
		
		//Get week epochs and test which is greater
		$thisWeek = $this->getWeekEpoch() ;
		$thatWeek = $week->getWeekEpoch() ;
		
		return ($thisWeek > $thatWeek) ;
	}
	
	/**
	 * This method will return true if the given $week is greater than $this week
	 * 
	 * @param TermWeek|string|integer $week The week to test is after $this week as either a week constant, an epoch week or a TermWeek object
	 * 
	 * @return boolean    Returns true if $week is after $this, otherwise false
	 */
	public function lessThan($week)
	{
		if(is_string($week) or is_numeric($week))
			$week = $this->term->getWeek($week) ;
		elseif(!$week instanceof TermWeek)
			throw new TermException("Invalid \$week provided",TermException::INPUT_ERROR) ;
		
		//Get week epochs and test which is greater
		$thisWeek = $this->getWeekEpoch() ;
		$thatWeek = $week->getWeekEpoch() ;
		
		return ($thisWeek < $thatWeek) ;
	}
	
	/**
	 * This method will return true if the given $week is equal to $this week
	 * 
	 * @param TermWeek|string|integer $week The week to test is equal to this week as either a week constant, an epoch week or a TermWeek object
	 * 
	 * @return boolean    Returns true if $week is equal to $this, otherwise false
	 */
	public function equals($week)
	{
		if(is_string($week) or is_numeric($week))
			$week = $this->term->getWeek($week) ;
		elseif(!$week instanceof TermWeek)
			throw new TermException("Invalid \$week provided",TermException::INPUT_ERROR) ;
		
		//Get week epochs and test which is greater
		$thisWeek = $this->getWeekEpoch() ;
		$thatWeek = $week->getWeekEpoch() ;
		
		return ($thisWeek == $thatWeek) ;
	}
	
	
	/**
	 * This method returns a TermWeek for the following week, or null if the last week
	 * 
	 * @return TermWeek    Returns a TermWeek object for the following week or null
	 */
	public function getNextWeek()
	{
		try
		{
			return $this->term->getWeek($this->weekEpoch+1) ;
		}
		catch(TermException $e)
		{
			if($e->getCode() == TermException::INVALID_EPOCH_WEEK)
				return null ;
			throw $e ;
		}
	}
}



?>