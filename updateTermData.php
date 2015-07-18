<?php

/**
 * This script reads the etc/term.ini file and serializes it into termData.serialized
 * file for use by the Term and TermWeek classes.  Run this script after updating
 * the etc/term.ini file with new term information
 */
require_once('Term.php') ;

Term::createSerializedData() ;

?>
