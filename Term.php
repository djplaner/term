<?php

require_once('TermWeek.php') ;

/**
 * A class for handling University/School terms and weeks
 * 
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * @author Damien Clark <damo.clarky@gmail.com>
 * @author Colin Beer <colinwbeer@gmail.com>
 */
class Term
{
	//Week constants
	const PRE_WEEK2 = 'p2' ;
	const PRE_WEEK1 = 'p1' ;
	const WEEK1 = 'w1' ;
	const WEEK2 = 'w2' ;
	const WEEK3 = 'w3' ;
	const WEEK4 = 'w4' ;
	const WEEK5 = 'w5' ;
	const WEEK6 = 'w6' ;
	const WEEK7 = 'w7' ;
	const WEEK8 = 'w8' ;
	const WEEK9 = 'w9' ;
	const WEEK10 = 'w10' ;
	const WEEK11 = 'w11' ;
	const WEEK12 = 'w12' ;
	const WEEK13 = 'w13' ;
	const WEEK14 = 'w14' ;
	const WEEK15 = 'w15' ;
	const WEEK16 = 'w16' ;
	const WEEK17 = 'w17' ;
	const WEEK18 = 'w18' ;
	const WEEK19 = 'w19' ;
	const WEEK20 = 'w20' ;
	const BREAK_WEEK1 = 'b1' ;
	const BREAK_WEEK2 = 'b2' ;
	const BREAK_WEEK3 = 'b3' ;

	/**
	 * @var array An array of week constants mapped to abbreviated week names (ie week 1 expressed as single digit 1)
	 */
	protected $shortWeekName = array
	(
		Term::PRE_WEEK2 => '-2',
		Term::PRE_WEEK1 => '-1',
		Term::WEEK1 => '1',
		Term::WEEK2 => '2',
		Term::WEEK3 => '3',
		Term::WEEK4 => '4',
		Term::WEEK5 => '5',
		Term::WEEK6 => '6',
		Term::WEEK7 => '7',
		Term::WEEK8 => '8',
		Term::WEEK9 => '9',
		Term::WEEK10 => '10',
		Term::WEEK11 => '11',
		Term::WEEK12 => '12',
		Term::WEEK13 => '13',
		Term::WEEK14 => '14',
		Term::WEEK15 => '15',
		Term::WEEK16 => '16',
		Term::WEEK17 => '17',
		Term::WEEK18 => '18',
		Term::WEEK19 => '19',
		Term::WEEK20 => '20',
		Term::BREAK_WEEK1 => 'B1',
		Term::BREAK_WEEK2 => 'B2',
		Term::BREAK_WEEK3 => 'B3'
	) ;
	
	/**
	 * @var array An array of week constants mapped to formal long week names (ie week 1 expressed as 'Week 1')
	 */
	protected $weekName = array
	(
		Term::PRE_WEEK2 => 'Week -2',
		Term::PRE_WEEK1 => 'Week -1',
		Term::WEEK1 => 'Week 1',
		Term::WEEK2 => 'Week 2',
		Term::WEEK3 => 'Week 3',
		Term::WEEK4 => 'Week 4',
		Term::WEEK5 => 'Week 5',
		Term::WEEK6 => 'Week 6',
		Term::WEEK7 => 'Week 7',
		Term::WEEK8 => 'Week 8',
		Term::WEEK9 => 'Week 9',
		Term::WEEK10 => 'Week 10',
		Term::WEEK11 => 'Week 11',
		Term::WEEK12 => 'Week 12',
		Term::WEEK13 => 'Week 13',
		Term::WEEK14 => 'Week 14',
		Term::WEEK15 => 'Week 15',
		Term::WEEK16 => 'Week 16',
		Term::WEEK17 => 'Week 17',
		Term::WEEK18 => 'Week 18',
		Term::WEEK19 => 'Week 19',
		Term::WEEK20 => 'Week 20',
		Term::BREAK_WEEK1 => 'Break Week 1',
		Term::BREAK_WEEK2 => 'Break Week 2',
		Term::BREAK_WEEK3 => 'Break Week 3'
	) ;
	
	/**
	 * @var string The term this instance relates to expressed as a strm
	 */
	protected $strm = null ;
	
	/**
	 * @var array Data structure holding term information for all terms
	 */
	static protected $termData = null ;

	/**
	 * @var string Filename for the serialized term data relative to this file
	 */
	const TERM_DATA_FILENAME = 'etc/termData.serialized' ;
	
	/**
	 * Constructor for Term class.  Takes a term expressed as a strm or a DateTime
	 * that falls within the Term.  If term provided is null
	 * it will construct a Term class for the current term based on the current date
	 * 
	 * @param string|DateTime $term The term to create expressed as either a strm or a DateTime
	 * 
	 * @return Term    The Term class
	 */
	function __construct($term=null)
	{
		//Load term data from serialized file
		if(Term::$termData == null)
			$this->loadTermData() ;
		
		//If $term is numeric, then interpret as the strm
		if(is_numeric($term))
			$strm = $term ;
		elseif($term instanceof DateTime or $term == null)
		{
			//If nothing passed in, then use current date to find a term
			//Otherwise, if a DateTime was passed in, use that to find a term
			if($term == null)
				$date = new DateTime() ; //Look for what term it is now
			else 
				$date = $term ; //Otherwise, look for term at given date

			$dateWeek = Term::calculateWeekEpoch($date) ;

			//Check to see if we have term data for given date
			//If not, it may be between terms, so search backwards for the previous term
			$searchWeek = $dateWeek ;
			//@todo The following loop is broken
			while(!array_key_exists($searchWeek,Term::$termData['terms']))
			{
				if($searchWeek < 1) //We have a serious problem - this will prevent infinite loop
					throw new TermException("Unknown term for epoch week $dateWeek with current date/time ".$date->format(DateTime::RFC822),TermException::INPUT_ERROR) ;
				$searchWeek-- ;
			}
			
			//Get the strm for the given week (or the closest week we could find searching backwards)
			$strm = Term::$termData['terms'][$searchWeek] ;
		}
		
		//Make sure we have been passed a valid strm otherwise throw exception
		if(!$this->validateStrm($strm))
			throw new TermException("$strm is not a valid strm",TermException::INPUT_ERROR) ;
		
		//Check to see if we have data for this strm
		if(!array_key_exists($strm,Term::$termData['strms']))
			throw new TermException("No data defined in ini file for strm $strm",TermException::NO_STRM_DATA) ;
		
		$this->strm = $strm ;
		
	}

	/**
	 * This method will load term data from serialized file
	 *
	 * @throws TermException If there is an error unserializing the term data
	 */
	protected function loadTermData()
	{
		//Get the contents of the serialized data from file relative to this file Term.php
		$data = file_get_contents(dirname(__FILE__) . '/' . Term::TERM_DATA_FILENAME) ;
		if($data === false)
			throw new TermException('Error retrieving contents of file pathtofile',TermException::INPUT_OUTPUT_ERROR) ;
		
		Term::$termData = unserialize($data) ;
		if(Term::$termData === false)
			throw new TermException("Error unserializing the following string: $data",TermException::INPUT_ERROR) ;
	}
	
	/**
	 * This method will return an array of Term objects spanning $startTerm to $endTerm
	 * inclusive.  $startTerm and $endTerm can be represented as either Term objects
	 * or strms.
	 *
	 * If either $startTerm or $endTerm are null, then it will default to the present
	 * term
	 *
	 * @static
	 * 
	 * @param string|Term $startTerm The starting Term
	 * @param string|Term $endTerm   The ending Term
	 * 
	 * @return array    An array of Term objects
	 */
	static function getTermRange($startTerm=null,$endTerm=null)
	{
		//If both start and end are null, then barf
		if($startTerm == null and $endTerm == null)
			throw new TermException('Both $startTerm and $endTerm are null - only one of these parameters may be null',TermException::INPUT_ERROR) ;
		//Set start or end as present term, if given as null
		elseif($startTerm == null)
			$startTerm = new Term() ;
		elseif($endTerm == null)
			$endTerm = new Term() ;
			
		//Create the $st starting Term object
		if($startTerm instanceof Term)
			$st = $startTerm ;
		else
			$st = new Term($startTerm) ;
			
		//Create the $et ending Term object
		if($endTerm instanceof Term)
			$et = $endTerm ;
		else
			$et = new Term($endTerm) ;
		
		//Create array of Term objects to return
		$output = array($st) ;
		
		//Check to make sure that the $startTerm is before the $endTerm
		if($st->getStrm() > $et->getStrm())
			throw new TermException('$startTerm must be before $endTerm',TermException::INPUT_ERROR) ;
		
		//Iterate over the Term objects, getting the next one in sequence while
		//we haven't yet made it to the last term
		while($st->getStrm() < $et->getStrm())
		{
			$st = $st->getNextTerm() ;
			$output[] = $st ;
		}
		return $output ;
	}
	
	/**
	 * This method will return an array of strm values representing terms spanning
	 * $startTerm to $endTerm inclusive.  $startTerm and $endTerm can be represented
	 * as either Term objects or strms.
	 *
	 * If either $startTerm or $endTerm are null, then it will default to the present
	 * term.
	 *
	 * @static
	 * 
	 * @param string|Term $startTerm The starting Term
	 * @param string|Term $endTerm   The ending Term
	 * 
	 * @return array    An array of strms
	 */
	static function getStrmRange($startTerm=null,$endTerm=null)
	{
		$terms = Term::getTermRange($startTerm,$endTerm) ;
		$output = array() ;
		foreach ($terms as $term)
		{
			$output[] = $term->getStrm() ;
		}
		
		return $output ;
	}
	
	/**
	 * This method takes a term object and tests if it is the same term as this
	 * instance.  In other words, it tests to see if the terms are equal (ie have
	 * the same strm).  If $term is null, will test if this instance is equal to
	 * the current term, based on the present date
	 * 
	 * @param Term $term The term to test against this term for equality
	 * 
	 * @return boolean    True if they are the same term, otherwise false
	 */
	public function equals($term=null)
	{
		//If no term given, compare whether $this term is equal to the present calendar term
		if($term == null)
			$term = new Term() ;

		return ($this->getStrm() == $term->getStrm()) ;
	}
	
	/**
	 * This method determines based on the calendar date whether this term is the
	 * current term.
	 * 
	 * @return boolean    Returns true if $this term is the present term, otherwise false
	 */
	public function isCurrentTerm()
	{
		return $this->equals() ;
	}
	
	
	/**
	 * This method will return the strm as a string for this Term instance
	 * 
	 * @return string    The term as a strm
	 */
	public function getStrm()
	{
		return $this->strm ;
	}
	
	/**
	 * This method returns the year of this term expressed as 4 digit number
	 * 
	 * @return integer    The year as a 4 digit number
	 */
	public function getYear()
	{
		//Split the strm into 3 components (century,year,term)
		$a = $this->splitStrm() ;
		
		
		if($a[0] == 1)
			//If century is '1', then 
			$year = '19' ;
		else
			//Otherwise century will be a 2, thus the century is 20
			$year = '20' ;
			
		//Append the last two digits of the year, with 0 padding
		$year .= sprintf('%02d',$a[1]) ;
		
		//Make its type an integer
		return intval($year) ;
	}
	
	/**
	 * This method returns the term value of this term, expressed as a number (eg. 1,2,3 etc)
	 * 
	 * @return integer    The term expressed as a number
	 */
	public function getTermNumber()
	{
		//Split the strm into 3 components (century,year,term)
		$a = $this->splitStrm() ;
		//Return the term element as an integer value
		return intval($a[2]) ;
	}
	
	/**
	 * This method returns the term expressed as "Term t, yyyy" - eg "Term 1, 2014"
	 * 
	 * @return string    The term expressed as "Term t, yyyy"
	 */
	public function getTermName()
	{
		$termNumber = $this->getTermNumber() ;
		$termYear = $this->getYear() ;
		
		return "Term $termNumber, $termYear" ;
	}
	
	
	/**
	 * This method will return a Term instance for the term following this instance
	 * 
	 * @return Term    The next term
	 */
	public function getNextTerm()
	{
		//Try to get the next term
		try
		{
			$nextTerm = new Term($this->getNextStrm()) ;
		}
		catch(TermException $e)
		{
			//If there is no strm data in the ini file for the given strm, catch
			//this error, and simply return null, which makes sense if you are iterating
			//using the getNextTerm method (it will return false when there are no more terms)
			if($e->getCode() == TermException::NO_STRM_DATA)
				return null ;
			throw $e ;
		}
		//Otherwise, if we got a valid term object, return it
		return $nextTerm ;
	}

	/**
	 * This method will return a DateTime object representing the very start of term
	 * being week p2 (2 weeks out from Week 1)
	 *
	 * @return DateTime    DateTime representing the start of term (week p2)
	 */
	public function getTermStartDateTime()
	{
		//Get the week 2 weeks out from start of term
		$week = $this->getWeek(Term::PRE_WEEK2) ;
		//Return the start DateTime for 2 weeks out from start of term
		return $week->getWeekStart() ;
	}

	/**
	 * This method will return a DateTime object representing the very end of term
	 * being the end of week 12
	 * 
	 * @return DateTime    DateTime representing the end of term (end of week 12)
	 */
	public function getTermEndDateTime()
	{
		//Get the week12 week
		$week = $this->getWeek(Term::WEEK12) ;
		//Return the end DateTime for week 12
		return $week->getWeekEnd() ;
	}

	/**
	 * This method will return an integer representing the very start of term as
	 * a UNIX epoch (number of seconds since 1/1/1970) being week p2 (2 weeks out
	 * from Week 1)
	 * 
	 * @return integer    Integer representing start of term (week p2)
	 */
	public function getTermStartAsUnixEpoch()
	{
		return $this->getTermStartDateTime()->format('U') ;
	}
	
	/**
	 * This method will return an integer representing the very end of term as a
	 * UNIX epoch (number of seconds since 1/1/1970) being the end of week 12
	 * 
	 * @return integer    Integer representing the end of term (end of week 12)
	 */
	public function getTermEndAsUnixEpoch()
	{
		return $this->getTermEndDateTime()->format('U') ;
	}
	
	////////////////////////////////////////////////////////////////////////////////	
	//Week Request Methods
	////////////////////////////////////////////////////////////////////////////////
	
	/**
	 * This method will return a week for this term, identified either by epoch week
	 * or by week code or by a DateTime
	 * 
	 * @param string|integer|DateTime $week The week expressed as either an epoch week (no. weeks since 5/1/1970) or a week code (eg. 'w1') or a DateTime object
	 *
	 * @throws TermException If the $week is invalid, a TermException is thrown
	 * 
	 * @return TermWeek    A Term week instance
	 */
	public function getWeek($week)
	{
		if(is_numeric($week))
			$weekCode = $this->weekEpochToWeekCode($week) ;
		elseif(is_string($week))
			$weekCode = $week ;
		elseif($week instanceof DateTime)
			$weekCode = $this->weekEpochToWeekCode(Term::calculateWeekEpoch($week)) ;
		elseif($week == null)
			return $this->getCurrentWeek() ;
		else
			throw new TermException("Invalid \$week value provided: '$week'",TermException::INPUT_ERROR) ;

		$shortname = $this->shortWeekName[$weekCode] ;
		$name = $this->weekName[$weekCode] ;
		
		//Create a week instance and return
		$weekObject = new TermWeek(Term::$termData,$this,$weekCode,$shortname,$name) ;
		return $weekObject ;
	}
	
	/**
	 * This method returns the current week of this term
	 *
	 * @throws TermException If this term object isn't a current term, then calling this method will throw a TermException
	 * @return TermWeek    The TermWeek object for the current week
	 */
	public function getCurrentWeek()
	{
		//Get the present week's week epoch
		$now = new DateTime() ;
		$nowWeekEpoch = Term::calculateWeekEpoch($now) ;
		return $this->getWeek($nowWeekEpoch) ;
	}
	
	/**
	 * This method takes a start week and end week expressed as either epoch week
	 * or a week code. It then returns an array of TermWeek objects spanning
	 * $startWeek and $endWeek inclusively.
	 *
	 * If either $startWeek or $endWeek is null, it will default to the current
	 * week
	 * 
	 * @param integer|string $startWeek Start of week expressed as a week code or epoch week
	 * @param integer|string $endWeek   End of week expressed as a week code or epoch week
	 * 
	 * @return array    An array of TermWeek objects
	 */
	public function getWeekRange($startWeek=null,$endWeek=null)
	{
		$start = $this->getWeek($startWeek) ;
		$end = $this->getWeek($endWeek) ;
		
		$startWeekEpoch = $start->getWeekEpoch() ;
		$endWeekEpoch = $end->getWeekEpoch() ;
		
		$data = array() ;
		for($i=$startWeekEpoch;$i<=$endWeekEpoch;$i++)
		{
			$data[] = $this->getWeek($i) ;
		}
		
		return $data ;
	}
	
	/**
	 * This method takes a start week and end week expressed as either epoch week
	 * or a week code.  I then returns an array of Week codes spanning $startWeek
	 * and $endWeek inclusively.
	 *
	 * if either $startWeek or $endWeek is null, it will default to the current week
	 * 
	 * @param integer|string $startWeek Start of week expressed as a week code or epoch week
	 * @param integer|string $endWeek   End of week expressed as a week code or epoch week
	 * 
	 * @return array    An array of Term codes
	 */
	public function getWeekCodeRange($startWeek=null,$endWeek=null)
	{
		$weeks = $this->getWeekRange($startWeek,$endWeek) ;
		$data = array() ;
		foreach($weeks as $week)
		{
			$data[] = $week->getWeekCode() ;
		}
		return $data ;
	}
	
	
	////////////////////////////////////////////////////////////////////////////////
	//Support Methods
	////////////////////////////////////////////////////////////////////////////////

	/**
	 * This method will take a week epoch value and return a week code relative to this term
	 * 
	 * @param integer $weekEpoch Week expressed as a week epoch
	 * 
	 * @return string    Week expressed as a week code
	 */
	protected function weekEpochToWeekCode($weekEpoch)
	{
		if(!is_numeric($weekEpoch))
			throw new TermException("Invalid \$weekEpoch '$weekEpoch' - must be an integer",TermException::INPUT_ERROR) ;
		
		//Check to see if the given $weekEpoch exists in the Term::$termData array structure
		//If not, then throw an exception with code INVALID_EPOCH_WEEK so it can be handled if needs be
		if(!$this->array_keys_exist(array('strms',$this->strm,'weeks',$weekEpoch),Term::$termData))
			throw new TermException("The given epoch week $weekEpoch is not within term this term: ".$this->strm,TermException::INVALID_EPOCH_WEEK) ;

		//Otherwise, get the week code for this week epoch and return it
		$weekCode = Term::$termData['strms'][$this->strm]['weeks'][$weekEpoch] ;
		return $weekCode ;
	}
	
	/**
	 * This method will take a week code value relative to this term and return a week epoch 
	 * 
	 * @param string $weekCode Week expressed as a week code (eg. Term::WEEK1)
	 *
	 * @todo This method is untested
	 * 
	 * @return integer    Week epoch value
	 */
	protected function weekCodeToWeekEpoch($weekCode)
	{
		if(!is_string($weekCode))
			throw new TermException("Invalid \$weekCode '$weekCode' - must be a string see Term:: constants",TermException::INPUT_ERROR) ;
		
		//Flip the keys and values so the array is indexed by weekcode instead
		$flipped = array_flip(Term::$termData['strms'][$this->strm]['weeks']) ;

		//Check to see if the given $weekCode exists as a key in the Term::$termData array structure (after its flipped)
		//If not, then throw an exception with code INVALID_EPOCH_WEEK so it can be handled if needs be
		if(!array_key_exists($weekCode,$flipped))
			throw new TermException("The given week code $weekCode is not valid within term this term: ".$this->strm,TermException::INVALID_WEEK_CODE) ;

		//Otherwise, get the week epoch for this week code and return it
		$weekEpoch = $flipped[$weekCode] ;
		return $weekEpoch ;
	}
	
	/**
	 * Create serialized data file for term.ini and store in Term::TERM_DATA_FILENAME
	 * 
	 */
	static function createSerializedData()
	{
		$termini=parse_ini_file(dirname(__FILE__)."/etc/term.ini",true);
		
		$outputArray=array();
		
		foreach($termini as $strm=>$data)
		{
			//Initialise our generated data structure $outputArray
			$outputArray['strms'][$strm]=array();
			//Get the DateTime for week1 of term
			$startDateTime= DateTime::createFromFormat('j/n/Y H:i:s',$data['w1']." 00:00:00");
			//Get the epoch week for week1 of term
			$epochWeek1 = Term::calculateWeekEpoch($startDateTime);
			//Initialise our break weeks array so we can capture which epoch weeks are break weeks
			$breakWeekArray=array();
			//Iterate over the dates given for this term eg($key = w1, $value = '2014-02-14')
			foreach($data as $key=>$value)
			{
				//If we find a break week identifier (eg. b1, b2, ...)
				if(substr($key,0,1) == "b")
				{
					//Get the DateTime for that week
					$break = DateTime::createFromFormat('j/n/Y H:i:s',$value." 00:00:00");
					//Calculate the week epoch
					$weekEpoch =  Term::calculateWeekEpoch($break);
					//Add it to our list of break weeks
					array_push($breakWeekArray,$weekEpoch);
					//Set the week constant for our generated data structure
					$outputArray['strms'][$strm]['weeks'][$weekEpoch]=$key;
				}
			}
			//Set the 2 weeks prior to week 1
			$outputArray['strms'][$strm]['weeks'][($epochWeek1-2)]='p2';
			$outputArray['strms'][$strm]['weeks'][($epochWeek1-1)]='p1';

			//Determine how many weeks there are in this term from week1 until 2 weeks
			//prior to the following term (if possible)
			
			//Get the strm for next term (if we have it)
			$nextStrm = self::calculateNextStrm($strm) ;
			//If we don't have it, then just make this term 20 weeks long - should be long enough
			if(!array_key_exists($nextStrm,$termini))
				$weekEpochEnd = $epochWeek1 + 20 ; //We don't know when the next term starts because its not in the ini file yet so just make it 20 weeks long should be enough
			else
				//Otherwise $weekEpochEnd is 2 weeks prior to w1 of next term
				$weekEpochEnd = self::calculateWeekEpoch
				(
					DateTime::createFromFormat('j/n/Y H:i:s',$termini[$nextStrm]['w1']." 00:00:00")
				) - 2 ; //Take off the pre-weeks 1 and 2 for beginning of next term

			//Now let's start setting the rest of the week epoch values and their week constants
			//for this term, starting with week 1
			$week = 1 ;
			for($i=$epochWeek1;$i<$weekEpochEnd;$i++)
			{
				//If $i epoch week is a break week, just skip
				if(in_array($i,$breakWeekArray))
					continue;
				//Otherwise, its a legitimate week, so add it to our generated data structure
				$outputArray['strms'][$strm]['weeks'][$i]="w".$week++;
			}
			//Add census date to our generated data structure from the ini file
			$outputArray['strms'][$strm]['census']= DateTime::createFromFormat('j/n/Y H:i:s',$data['census']." 23:59:59") ;
		}
		//Initialise our terms array structure, which maps epoch weeks to strms
		//This is so we can work out from an epoch week what term it is
		$outputArray['terms']=array();
		foreach($outputArray['strms'] as $strm=>$data)
		{
			//echo "Strm=$strm\n";
			//print_r($data);
			foreach($data['weeks'] as $epochWeek=>$value)
			{
				//There should not be an overlap of epoch weeks between terms
				//In other words, an epoch week should not match more than one strm
				//If there is an overlap, throw an exception
				if(array_key_exists($epochWeek,$outputArray['terms']))
					throw new TermException("epoch week $epochWeek already found in terms array for term $strm",TermException::INVALID_EPOCH_WEEK) ;

				$outputArray['terms'][$epochWeek]=$strm;
			}
		 }
		//print_r($outputArray) ;
		//Write back data structure to serialized file
		file_put_contents(dirname(__FILE__) . '/' . Term::TERM_DATA_FILENAME,serialize($outputArray)) ;
	}

		/**
	 * This static method takes a DateTime object or UNIX Epoch and will return an
	 * integer representing the number of weeks since the 5th of January 1970.
	 *
	 * @static
	 * 
	 * @param DateTime|integer $date The date from which to determine the week epoch
	 * 
	 * @return integer    The number of weeks since 5th of Jan 1970
	 */
	public static function calculateWeekEpoch($date)
	{
		//Validate input to make sure we have a unix epoch or a DateTime
		if(!is_numeric($date) and !($date instanceof DateTime))
			throw new TermException('$date must be a Unix epoch as an integer or a DateTime object',TermException::INPUT_ERROR) ;
		
		//Get $date as unix epoch if given DateTime object
		if($date instanceof DateTime)
			$unixEpoch = $date->format('U') ;
		else
			$unixEpoch = $date ;
		
		//Get current date/time (with TZ)
		$d = new DateTime() ;
		//Get the TZ offset from UTC in seconds (in UTC+10, the offset val is 36000)
		$offset = $d->getTimezone()->getOffset($d) ;
		//(unix epoch - (4 days (345600) - TZ offset seconds)) divided by 7 days (/604800)
		$weekEpoch = intval(floor(($unixEpoch-(345600-$offset)) / 604800)) ;
		return $weekEpoch ;
	}
	
	/**
	 * This static method will return a DateTime object representing the date and time
	 * for the start of the given epoch week (number of weeks since 5/1/1970 midnight localtime)
	 *
	 * @static
	 * 
	 * @param integer $epochWeek The epoch week
	 * 
	 * @return DateTime    The date and time for the start of the given epoch week
	 */
	public static function epochWeekToDate($epochWeek)
	{
		//Get epoch week start date
		$epochDate=new DateTime('1970-01-05 00:00:00');
		//Add $epochWeek weeks to start date
		$epochDate->add(new DateInterval("P{$epochWeek}W")) ;
		//Return the resulting date for start of $epochWeek
		return $epochDate ;
	}
	
	/**
	 * This static method will return a unix epoch (num seconds since 1/1/1970 midnight UTC)
	 * for the given epoch week (number of weeks since 5/1/1970 midnight localtime)
	 *
	 * @static
	 * 
	 * @param integer $epochWeek The epoch week as described
	 *
	 * @return integer    Unix epoch for $epochWeek
	 */
	public static function epochWeekToUnixEpoch($epochWeek)
	{
		if(!isset($epochWeek))
			throw new TermException("No epochWeek provided to epochWeekToUnixEpoch",TermException::INPUT_ERROR) ;
		
		$date = Term::epochWeekToDate($epochWeek) ;
		return $date->format('U') ;
	}
	
	
	/**
	 * This method evaluates true if the $strm is a valid term represented as a strm
	 * 
	 * @param string $strm The term as a strm
	 *
	 * @see preg_match
	 * 
	 * @return boolean|integer    Returns 1 if $strm is a valid term, 0 if not a valid strm and FALSE if an error occurred
	 */
	protected function validateStrm($strm)
	{
		return(preg_match('/^[12][0-9][0-9][1-6]$/',$strm)) ;
	}

	/**
	 * This method splits the strm into 3 components (century, 2 digit year, term number)
	 * as an array. Eg. array(2,'06',3) - term 3, 2006
	 * 
	 * @param string $strm The term expressed as a strm
	 * 
	 * @return array    An array representation of the strm as (century, 2 digit year, term number)
	 */
	protected function splitStrm($strm=null)
	{
		if($strm == null)
			$strm = $this->strm ;
		return self::calculateSplitStrm($strm) ;
	}

	/**
	 * This static method splits the strm into 3 components (century, 2 digit year, term number)
	 * as an array. Eg. array(2,'06',3) - term 3, 2006
	 * 
	 * @param string $strm The term expressed as a strm
	 *
	 * @throws TermException
	 * 
	 * @return array    An array representation of the strm as (century, 2 digit year, term number)
	 */
	protected static function calculateSplitStrm($strm)
	{
		if($strm == null)
			throw new TermException("No \$strm provided",TermException::INPUT_ERROR) ;
		
		if(!is_numeric($strm))
			throw new TermException("Invalid \$strm provided: $strm",TermException::INPUT_ERROR) ;

		$a = array() ;
		if(!preg_match('/^([12])([0-9][0-9])([1-6])$/',$strm,$a))
			throw new TermException("Invalid strm: $strm",TermException::INPUT_ERROR) ;
		
		array_shift($a) ; //Get rid of first element - it matching everything
				
		return $a ;
	}

	/**
	 * This static function takes a term expressed as a strm and returns a strm representing
	 * the following term
	 * 
	 * @param string $strm The term as a strm. If null, throws an exception
	 *
	 * @throws TermException If invalid strm provided
	 * 
	 * @return string    The term that follows $strm as a strm
	 */
	protected static function calculateNextStrm($strm)
	{
		if($strm == null)
			throw new TermException("No \$strm provided",TermException::INPUT_ERROR) ;
		
		if(!is_numeric($strm))
			throw new TermException("Invalid \$strm provided: $strm",TermException::INPUT_ERROR) ;

		/**
		 * @var array An array of the last 3 digits of the strm
		 */
		$a = self::calculateSplitStrm($strm) ;
		
		//Increment the term (roll over to 1 again from 3)
		$a[2] = ($a[2] % 3) + 1 ;
	
		//If we rolled over to one, then increment the year
		if($a[2] == 1)
		{
			$a[1]++ ;
			$a[1] = sprintf('%02d',$a[1]) ; //Prefix with 0
		}
	
		//Stitch back up
		return join('',$a) ;
	}
	
	/**
	 * This method takes a term expressed as a strm and returns a strm representing
	 * the following term
	 * 
	 * @param string $strm The term as a strm. If null, uses the strm for this term instance
	 * 
	 * @return string    The term that follows $strm as a strm
	 */
	protected function getNextStrm($strm=null)
	{
		if($strm == null)
			$strm = $this->strm ;
		return self::calculateNextStrm($strm) ;
	}

	/**
	 * Do deep check for existence of array key
	 * 
	 * @param array $keys   An array containing key values to look up $array in the order of nesting
	 * @param array $array Multi-dimensional array to search through each dimension by matching values in $keys
	 *
	 * array_keys_exist(array(12345,12,34),$multiDimensionalArray)
	 *
	 * Will return true if exists:
	 * $multiDimensionalArray[12345][12][34]
	 * 
	 * @return boolean    True if all the $keys are found otherwise false
	 */
	private function array_keys_exist($keys,$array)
	{
		$a = $array ;
		foreach($keys as $key)
		{
			if(!array_key_exists($key,$a))
				return false ;
			$a = $a[$key] ;
		}
		return true ;
	}

}


/**
 * Exception Handler class for Term and termWeek classes
 * 
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * @author Damien Clark <damo.clarky@gmail.com>
 * @author Colin Beer <colinwbeer@gmail.com>
 */
class TermException extends Exception
{
	/**
	 * @var integer A value passed into a method is invalid
	 */
	const INPUT_ERROR = 1 ;
	/**
	 * @var integer	An error occurred reading/writing a file
	 */
	const INPUT_OUTPUT_ERROR = 2 ;
	/**
	 * @var integer An epoch week value was not found in the term data structure
	 */
	const INVALID_EPOCH_WEEK = 3 ;
	/**
	 * @var integer An invalid week code was provided and not found in the term data structure
	 */
	const INVALID_WEEK_CODE = 4 ;
	/**
	 * @var integer	There is no data in the term.ini file for the given strm
	 */
	const NO_STRM_DATA = 5 ;
}




?>