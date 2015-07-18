

# Term #

An [academic term](http://www.australia.gov.au/about-australia/special-dates-and-events/school-term-dates) (or simply "term") is a portion of an academic year, the time during which an educational institution holds classes (Wikipedia, 2015).

Term is an open-source object-oriented PHP library that performs calendar calculations on academic terms for educational institutions. It draws it's schedule data from an .ini configuration file.  As new yearly term dates are finalised, this information is added to the configuration file.

## Requirements ##

The Term Library requires PHP 5.2 or greater.  It has no other library dependencies.  

## Synopsis ##

The library can perform the following calculations and functions on term dates:

* Given two Term objects, determine if they are equal (the same term)
* Determine if given Term object is the current term (based on the current calendar date)
* Iterate over a range of terms
* Retrieve calendar components of the Term, such as:
	* the year (2015),
	* term number (1),
	* term name (Term 1),
	* term start date (24/2/2014),
	* term end date (23/5/2014),
	* a TermWeek object by week number (week 4),
	* TermWeek object based on calendar date (week 4),
	* a range of TermWeek objects as an array (Week 4 - Week 8)
* Use UNIX Epoch date/time representations
* Use Week Epoch representations (Number of weeks since Monday the 5th January, 1970)

The library can also perform the following calculations and function on weeks within a given term:

* Get short name representation of a week (4)
* Get long name representation of a week (Week 4)
* Get start date for a week (Date of the Monday)
* Get end date for a week (Date of the Sunday)
* Iterate over a range of weeks
* Determine if given TermWeek object is the current week of term (based on the current calendar date)
* Determine if given TermWeek object is before, after or equal to another TermWeek

## Installation ##

Installation via git can be done as follows:

```bash
cd /var/www/lib
git clone https://github.com/damoclark/term
```

In the near future, it will be possible to install into your project using Composer (see [TODO](#todo) section), use the following Composer command:

```bash
cd /var/www/html/project
composer install mylms-tools/term
```

## Usage ##

### term.ini ###

The term.ini file contains the dates for terms in a given year
```ini
;2141 = (20)(14)(Term 1)
[2141]
;Week 1 starts on 24th of Feb
w1 = 24/2/2014
;Census date is the 18th of March
census = 18/3/2014 
;Break week 1 is 31st of March
b1 = 31/3/2014 

;2142 = (20)(14)(Term 2)
[2142]
w1 = 30/6/2014
census = 22/7/2014
b1 = 4/8/2014

;2141 = (20)(14)(Term 3)
[2143]
w1 = 27/10/2014
census = 18/11/2014
;First break week is 1st December
b1 = 1/12/2014
;Second non-continguous break week is 29th December
b2 = 29/12/2014

;2151 = (20)(15)(Term 1)
[2151]
...

```
### updateTermData.php ###

After changing the term.ini file, the ```updateTermData.php``` script needs to be executed.  It will read the term.ini and create a serialized php data structure in the file etc/termData.serialized.  The file is read by the Term class when it is loaded, rather than parsing the term.ini each time.

```bash
cd /var/www/lib/term
php updateTermData.php
```

### API Examples ###

```php
require_once('Term.php') ;

//Instantiate Term object for current term (based on calendar date)
$currentTerm = new Term() ;

//Instantiate Term object for Term 1, 2014
$selectedTerm = new Term('2141') ;

//If the current term isn't the selected term, ...
if(!$currentTerm->equals($selectedTerm))
//or
if(!$selectedTerm->isCurrentTerm())

//Output: Year=2014
echo "Year=" . $selectedTerm->getYear() . "\n" ;

//Output: 1
echo $selectedTerm->getTermNumber() . "\n" ;

//Output: Term 1, 2014
echo $selectedTerm->getTermName() . "\n" ;

//Get the term that follows $selectedTerm
//$next == 2142
$next = $selectedTerm->getNextTerm() ;

//2014-06-30T00:00:00
echo $selectedTerm->getTermStartDateTime()->format('c') . "\n" ;

//$week = TermWeek object for week 2 of term 2, 2014
$week = $selectedTerm->getWeek(2) ;

//Output: 2
echo $week->getWeekShortName() . "\n" ;

//Output: Week 2
echo $week->getWeekName() . "\n" ;

//Output: 2014-07-07T00:00:00
echo $week->getWeekStart()->format('c') . "\n" ;

//Output: w2
echo $week->getWeekCode() . "\n" ;

//Current week of current term, based on calendar date
$currentWeek = $currentTerm->getCurrentWeek() ;

//If the current week is equal to the selected week
if($currentWeek->equals($week))

//If the current week is after the selected week
if($currentWeek->greaterThan($week))

//$nextWeek = w3
$nextWeek = $week->getNextWeek() ;
```
## TODO ##

Make PSR-4 Compliant

Allow abstraction of the label "Term", such that educational institutions can use localised alternatives such as Semester, or Section.

Allow for variable length terms (the class currently is fixed to 12 week terms)

## Licence ##

Term is licenced under the terms of the [GPLv3](http://www.gnu.org/licenses/gpl-3.0.en.html).

## Contributions ##

Contributions are welcome - fork and push away.  Contact me ([Damien Clark](mailto:damo.clarky@gmail.com)) for further information.

