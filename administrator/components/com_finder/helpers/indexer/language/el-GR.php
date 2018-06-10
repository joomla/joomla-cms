<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/*
 * Copyright (c) 2009 Vassilis Spiliopoulos (http://www.psychfamily.gr),
 * Pantelis Nasikas under GNU General Public License Version 3
 * Updated for Drupal 6, 7 and Drupal 8 by
 * Yannis Karampelas (info@netstudio.gr) in 2011 and 2017 respectively.
 * This is a port of the php implementation of
 * Spyros Saroukos into Drupal CMS. Spyros Saroukos implementation
 * was based on the work of Panos Kyriakakis (http://www.salix.gr) and
 * Georgios Ntais (Georgios.Ntais@eurodyn.com)
 * Georgios firstly developed the stemmer's javascript implementation for his
 * master thesis at Royal Institute of Technology [KTH], Stockholm Sweden
 * http://www.dsv.su.se/~hercules/papers/Ntais_greek_stemmer_thesis_final.pdf
 *
 * !!!!!The encoding of this file is iso-8859-7 instead of UTF-8 on purpose!!!!!!!
 */

defined('_JEXEC') or die;

/**
 * Greek language support class for the Finder indexer package.
 *
 * @since  __DEPLOY_VERSION__
 */
class FinderIndexerLanguageel_GR extends FinderIndexerLanguage
{
	/**
	 * Method to stem a token.
	 *
	 * @param   string  $token  The token to stem.
	 *
	 * @return  string  The stemmed token.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function stem($token)
	{
		$w = $token;

		// Number of rules examined. Used for debugging and testing purposes.
		$numberOfRulesExamined = 0;

		$w_CASE = array(mb_strlen($w, 'UTF-8'));//1 for changed case in that position, 2 especially for Ï‚

		//first we must find all letters that are not in Upper case and store their position
		$unacceptedLetters = array(
			"α",
			"β",
			"γ",
			"δ",
			"ε",
			"ζ",
			"η",
			"θ",
			"ι",
			"κ",
			"λ",
			"μ",
			"ν",
			"ξ",
			"ο",
			"π",
			"ρ",
			"σ",
			"τ",
			"υ",
			"φ",
			"χ",
			"ψ",
			"ω",
			"ά",
			"έ",
			"ή",
			"ί",
			"ό",
			"ύ",
			"ς",
			"ώ",
			"ϊ",
		);
		$acceptedLetters   = array(
			"Α",
			"Β",
			"Γ",
			"Δ",
			"Ε",
			"Ζ",
			"Η",
			"Θ",
			"Ι",
			"Κ",
			"Λ",
			"Μ",
			"Ν",
			"Ξ",
			"Ο",
			"Π",
			"Ρ",
			"Σ",
			"Τ",
			"Υ",
			"Φ",
			"Χ",
			"Ψ",
			"Ω",
			"Α",
			"Ε",
			"Η",
			"Ι",
			"Ο",
			"Υ",
			"Σ",
			"Ω",
			"Ι",
		);

		for ($k = 0; $k <= 32; $k = $k + 1)
		{
			for ($i = 0; $i <= mb_strlen($w, 'UTF-8') - 1; $i++)
			{
				if ($w[$i] == $unacceptedLetters[$k])
				{
					if ($w[$i] == "ς")
					{
						$w[$i]      = "Σ";
						$w_CASE[$i] = 2;
					}
					else
					{
						$w[$i]      = $acceptedLetters[$k];
						$w_CASE[$i] = "1";
					}
				}
			}
		}
		//stop-word removal
		$numberOfRulesExamined++;
		$stop_words = '/^(ΕΚΟ|ΑΒΑ|ΑΓΑ|ΑΓΗ|ΑΓΩ|ΑΔΗ|ΑΔΩ|ΑΕ|ΑΕΙ|ΑΘΩ|ΑΙ|ΑΙΚ|ΑΚΗ|ΑΚΟΜΑ|ΑΚΟΜΗ|ΑΚΡΙΒΩΣ|ΑΛΑ|ΑΛΗΘΕΙΑ|ΑΛΗΘΙΝΑ|ΑΛΛΑΧΟΥ|ΑΛΛΙΩΣ|ΑΛΛΙΩΤΙΚΑ|ΑΛΛΟΙΩΣ|ΑΛΛΟΙΩΤΙΚΑ|ΑΛΛΟΤΕ|ΑΛΤ|ΑΛΩ|ΑΜΑ|ΑΜΕ|ΑΜΕΣΑ|ΑΜΕΣΩΣ|ΑΜΩ|ΑΝ|ΑΝΑ|ΑΝΑΜΕΣΑ|ΑΝΑΜΕΤΑΞΥ|ΑΝΕΥ|ΑΝΤΙ|ΑΝΤΙΠΕΡΑ|ΑΝΤΙΣ|ΑΝΩ|ΑΝΩΤΕΡΩ|ΑΞΑΦΝΑ|ΑΠ|ΑΠΕΝΑΝΤΙ|ΑΠΟ|ΑΠΟΨΕ|ΑΠΩ|ΑΡΑ|ΑΡΑΓΕ|ΑΡΕ|ΑΡΚ|ΑΡΚΕΤΑ|ΑΡΛ|ΑΡΜ|ΑΡΤ|ΑΡΥ|ΑΡΩ|ΑΣ|ΑΣΑ|ΑΣΟ|ΑΤΑ|ΑΤΕ|ΑΤΗ|ΑΤΙ|ΑΤΜ|ΑΤΟ|ΑΥΡΙΟ|ΑΦΗ|ΑΦΟΤΟΥ|ΑΦΟΥ|ΑΧ|ΑΧΕ|ΑΧΟ|ΑΨΑ|ΑΨΕ|ΑΨΗ|ΑΨΥ|ΑΩΕ|ΑΩΟ|ΒΑΝ|ΒΑΤ|ΒΑΧ|ΒΕΑ|ΒΕΒΑΙΟΤΑΤΑ|ΒΗΞ|ΒΙΑ|ΒΙΕ|ΒΙΗ|ΒΙΟ|ΒΟΗ|ΒΟΩ|ΒΡΕ|ΓΑ|ΓΑΒ|ΓΑΡ|ΓΕΝ|ΓΕΣ||ΓΗ|ΓΗΝ|ΓΙ|ΓΙΑ|ΓΙΕ|ΓΙΝ|ΓΙΟ|ΓΚΙ|ΓΙΑΤΙ|ΓΚΥ|ΓΟΗ|ΓΟΟ|ΓΡΗΓΟΡΑ|ΓΡΙ|ΓΡΥ|ΓΥΗ|ΓΥΡΩ|ΔΑ|ΔΕ|ΔΕΗ|ΔΕΙ|ΔΕΝ|ΔΕΣ|ΔΗ|ΔΗΘΕΝ|ΔΗΛΑΔΗ|ΔΗΩ|ΔΙ|ΔΙΑ|ΔΙΑΡΚΩΣ|ΔΙΟΛΟΥ|ΔΙΣ|ΔΙΧΩΣ|ΔΟΛ|ΔΟΝ|ΔΡΑ|ΔΡΥ|ΔΡΧ|ΔΥΕ|ΔΥΟ|ΔΩ|ΕΑΜ|ΕΑΝ|ΕΑΡ|ΕΘΗ|ΕΙ|ΕΙΔΕΜΗ|ΕΙΘΕ|ΕΙΜΑΙ|ΕΙΜΑΣΤΕ|ΕΙΝΑΙ|ΕΙΣ|ΕΙΣΑΙ|ΕΙΣΑΣΤΕ|ΕΙΣΤΕ|ΕΙΤΕ|ΕΙΧΑ|ΕΙΧΑΜΕ|ΕΙΧΑΝ|ΕΙΧΑΤΕ|ΕΙΧΕ|ΕΙΧΕΣ|ΕΚ|ΕΚΕΙ|ΕΛΑ|ΕΛΙ|ΕΜΠ|ΕΝ|ΕΝΤΕΛΩΣ|ΕΝΤΟΣ|ΕΝΤΩΜΕΤΑΞΥ|ΕΝΩ|ΕΞ|ΕΞΑΦΝΑ|ΕΞΙ|ΕΞΙΣΟΥ|ΕΞΩ|ΕΟΚ|ΕΠΑΝΩ|ΕΠΕΙΔΗ|ΕΠΕΙΤΑ|ΕΠΗ|ΕΠΙ|ΕΠΙΣΗΣ|ΕΠΟΜΕΝΩΣ|ΕΡΑ|ΕΣ|ΕΣΑΣ|ΕΣΕ|ΕΣΕΙΣ|ΕΣΕΝΑ|ΕΣΗ|ΕΣΤΩ|ΕΣΥ|ΕΣΩ|ΕΤΙ|ΕΤΣΙ|ΕΥ|ΕΥΑ|ΕΥΓΕ|ΕΥΘΥΣ|ΕΥΤΥΧΩΣ|ΕΦΕ|ΕΦΕΞΗΣ|ΕΦΤ|ΕΧΕ|ΕΧΕΙ|ΕΧΕΙΣ|ΕΧΕΤΕ|ΕΧΘΕΣ|ΕΧΟΜΕ|ΕΧΟΥΜΕ|ΕΧΟΥΝ|ΕΧΤΕΣ|ΕΧΩ|ΕΩΣ|ΖΕΑ|ΖΕΗ|ΖΕΙ|ΖΕΝ|ΖΗΝ|ΖΩ|Η|ΗΔΗ|ΗΔΥ|ΗΘΗ|ΗΛΟ|ΗΜΙ|ΗΠΑ|ΗΣΑΣΤΕ|ΗΣΟΥΝ|ΗΤΑ|ΗΤΑΝ|ΗΤΑΝΕ|ΗΤΟΙ|ΗΤΤΟΝ|ΗΩ|ΘΑ|ΘΥΕ|ΘΩΡ|Ι|ΙΑ|ΙΒΟ|ΙΔΗ|ΙΔΙΩΣ|ΙΕ|ΙΙ|ΙΙΙ|ΙΚΑ|ΙΛΟ|ΙΜΑ|ΙΝΑ|ΙΝΩ|ΙΞΕ|ΙΞΟ|ΙΟ|ΙΟΙ|ΙΣΑ|ΙΣΑΜΕ|ΙΣΕ|ΙΣΗ|ΙΣΙΑ|ΙΣΟ|ΙΣΩΣ|ΙΩΒ|ΙΩΝ|ΙΩΣ|ΙΑΝ|ΚΑΘ|ΚΑΘΕ|ΚΑΘΕΤΙ|ΚΑΘΟΛΟΥ|ΚΑΘΩΣ|ΚΑΙ|ΚΑΝ|ΚΑΠΟΤΕ|ΚΑΠΟΥ|ΚΑΠΩΣ|ΚΑΤ|ΚΑΤΑ|ΚΑΤΙ|ΚΑΤΙΤΙ|ΚΑΤΟΠΙΝ|ΚΑΤΩ|ΚΑΩ|ΚΒΟ|ΚΕΑ|ΚΕΙ|ΚΕΝ|ΚΙ|ΚΙΜ|ΚΙΟΛΑΣ|ΚΙΤ|ΚΙΧ|ΚΚΕ|ΚΛΙΣΕ|ΚΛΠ|ΚΟΚ|ΚΟΝΤΑ|ΚΟΧ|ΚΤΛ|ΚΥΡ|ΚΥΡΙΩΣ|ΚΩ|ΚΩΝ|ΛΑ|ΛΕΑ|ΛΕΝ|ΛΕΟ|ΛΙΑ|ΛΙΓΑΚΙ|ΛΙΓΟΥΛΑΚΙ|ΛΙΓΟ|ΛΙΓΩΤΕΡΟ|ΛΙΟ|ΛΙΡ|ΛΟΓΩ|ΛΟΙΠΑ|ΛΟΙΠΟΝ|ΛΟΣ|ΛΣ|ΛΥΩ|ΜΑ|ΜΑΖΙ|ΜΑΚΑΡΙ|ΜΑΛΙΣΤΑ|ΜΑΛΛΟΝ|ΜΑΝ|ΜΑΞ|ΜΑΣ|ΜΑΤ|ΜΕ|ΜΕΘΑΥΡΙΟ|ΜΕΙ|ΜΕΙΟΝ|ΜΕΛ|ΜΕΛΕΙ|ΜΕΛΛΕΤΑΙ|ΜΕΜΙΑΣ|ΜΕΝ|ΜΕΣ|ΜΕΣΑ|ΜΕΤ|ΜΕΤΑ|ΜΕΤΑΞΥ|ΜΕΧΡΙ|ΜΗ|ΜΗΔΕ|ΜΗΝ|ΜΗΠΩΣ|ΜΗΤΕ|ΜΙ|ΜΙΞ|ΜΙΣ|ΜΜΕ|ΜΝΑ|ΜΟΒ|ΜΟΛΙΣ|ΜΟΛΟΝΟΤΙ|ΜΟΝΑΧΑ|ΜΟΝΟΜΙΑΣ|ΜΙΑ|ΜΟΥ|ΜΠΑ|ΜΠΟΡΕΙ|ΜΠΟΡΟΥΝ|ΜΠΡΑΒΟ|ΜΠΡΟΣ|ΜΠΩ|ΜΥ|ΜΥΑ|ΜΥΝ|ΝΑ|ΝΑΕ|ΝΑΙ|ΝΑΟ|ΝΔ|ΝΕΐ|ΝΕΑ|ΝΕΕ|ΝΕΟ|ΝΙ|ΝΙΑ|ΝΙΚ|ΝΙΛ|ΝΙΝ|ΝΙΟ|ΝΤΑ|ΝΤΕ|ΝΤΙ|ΝΤΟ|ΝΥΝ|ΝΩΕ|ΝΩΡΙΣ|ΞΑΝΑ|ΞΑΦΝΙΚΑ|ΞΕΩ|ΞΙ|Ο|ΟΑ|ΟΑΠ|ΟΔΟ|ΟΕ|ΟΖΟ|ΟΗΕ|ΟΙ|ΟΙΑ|ΟΙΗ|ΟΚΑ|ΟΛΟΓΥΡΑ|ΟΛΟΝΕΝ|ΟΛΟΤΕΛΑ|ΟΛΩΣΔΙΟΛΟΥ|ΟΜΩΣ|ΟΝ|ΟΝΕ|ΟΝΟ|ΟΠΑ|ΟΠΕ|ΟΠΗ|ΟΠΟ|ΟΠΟΙΑΔΗΠΟΤΕ|ΟΠΟΙΑΝΔΗΠΟΤΕ|ΟΠΟΙΑΣΔΗΠΟΤΕ|ΟΠΟΙΔΗΠΟΤΕ|ΟΠΟΙΕΣΔΗΠΟΤΕ|ΟΠΟΙΟΔΗΠΟΤΕ|ΟΠΟΙΟΝΔΗΠΟΤΕ|ΟΠΟΙΟΣΔΗΠΟΤΕ|ΟΠΟΙΟΥΔΗΠΟΤΕ|ΟΠΟΙΟΥΣΔΗΠΟΤΕ|ΟΠΟΙΩΝΔΗΠΟΤΕ|ΟΠΟΤΕΔΗΠΟΤΕ|ΟΠΟΥ|ΟΠΟΥΔΗΠΟΤΕ|ΟΠΩΣ|ΟΡΑ|ΟΡΕ|ΟΡΗ|ΟΡΟ|ΟΡΦ|ΟΡΩ|ΟΣΑ|ΟΣΑΔΗΠΟΤΕ|ΟΣΕ|ΟΣΕΣΔΗΠΟΤΕ|ΟΣΗΔΗΠΟΤΕ|ΟΣΗΝΔΗΠΟΤΕ|ΟΣΗΣΔΗΠΟΤΕ|ΟΣΟΔΗΠΟΤΕ|ΟΣΟΙΔΗΠΟΤΕ|ΟΣΟΝΔΗΠΟΤΕ|ΟΣΟΣΔΗΠΟΤΕ|ΟΣΟΥΔΗΠΟΤΕ|ΟΣΟΥΣΔΗΠΟΤΕ|ΟΣΩΝΔΗΠΟΤΕ|ΟΤΑΝ|ΟΤΕ|ΟΤΙ|ΟΤΙΔΗΠΟΤΕ|ΟΥ|ΟΥΔΕ|ΟΥΚ|ΟΥΣ|ΟΥΤΕ|ΟΥΦ|ΟΧΙ|ΟΨΑ|ΟΨΕ|ΟΨΗ|ΟΨΙ|ΟΨΟ|ΠΑ|ΠΑΛΙ|ΠΑΝ|ΠΑΝΤΟΤΕ|ΠΑΝΤΟΥ|ΠΑΝΤΩΣ|ΠΑΠ|ΠΑΡ|ΠΑΡΑ|ΠΕΙ|ΠΕΡ|ΠΕΡΑ|ΠΕΡΙ|ΠΕΡΙΠΟΥ|ΠΕΡΣΙ|ΠΕΡΥΣΙ|ΠΕΣ|ΠΙ|ΠΙΑ|ΠΙΘΑΝΟΝ|ΠΙΚ|ΠΙΟ|ΠΙΣΩ|ΠΙΤ|ΠΙΩ|ΠΛΑΙ|ΠΛΕΟΝ|ΠΛΗΝ|ΠΛΩ|ΠΜ|ΠΟΑ|ΠΟΕ|ΠΟΛ|ΠΟΛΥ|ΠΟΠ|ΠΟΤΕ|ΠΟΥ|ΠΟΥΘΕ|ΠΟΥΘΕΝΑ|ΠΡΕΠΕΙ|ΠΡΙ|ΠΡΙΝ|ΠΡΟ|ΠΡΟΚΕΙΜΕΝΟΥ|ΠΡΟΚΕΙΤΑΙ|ΠΡΟΠΕΡΣΙ|ΠΡΟΣ|ΠΡΟΤΟΥ|ΠΡΟΧΘΕΣ|ΠΡΟΧΤΕΣ|ΠΡΩΤΥΤΕΡΑ|ΠΥΑ|ΠΥΞ|ΠΥΟ|ΠΥΡ|ΠΧ|ΠΩ|ΠΩΛ|ΠΩΣ|ΡΑ|ΡΑΙ|ΡΑΠ|ΡΑΣ|ΡΕ|ΡΕΑ|ΡΕΕ|ΡΕΙ|ΡΗΣ|ΡΘΩ|ΡΙΟ|ΡΟ|ΡΟΐ|ΡΟΕ|ΡΟΖ|ΡΟΗ|ΡΟΘ|ΡΟΙ|ΡΟΚ|ΡΟΛ|ΡΟΝ|ΡΟΣ|ΡΟΥ|ΣΑΙ|ΣΑΝ|ΣΑΟ|ΣΑΣ|ΣΕ|ΣΕΙΣ|ΣΕΚ|ΣΕΞ|ΣΕΡ|ΣΕΤ|ΣΕΦ|ΣΗΜΕΡΑ|ΣΙ|ΣΙΑ|ΣΙΓΑ|ΣΙΚ|ΣΙΧ|ΣΚΙ|ΣΟΙ|ΣΟΚ|ΣΟΛ|ΣΟΝ|ΣΟΣ|ΣΟΥ|ΣΡΙ|ΣΤΑ|ΣΤΗ|ΣΤΗΝ|ΣΤΗΣ|ΣΤΙΣ|ΣΤΟ|ΣΤΟΝ|ΣΤΟΥ|ΣΤΟΥΣ|ΣΤΩΝ|ΣΥ|ΣΥΓΧΡΟΝΩΣ|ΣΥΝ|ΣΥΝΑΜΑ|ΣΥΝΕΠΩΣ|ΣΥΝΗΘΩΣ|ΣΧΕΔΟΝ|ΣΩΣΤΑ|ΤΑ|ΤΑΔΕ|ΤΑΚ|ΤΑΝ|ΤΑΟ|ΤΑΥ|ΤΑΧΑ|ΤΑΧΑΤΕ|ΤΕ|ΤΕΙ|ΤΕΛ|ΤΕΛΙΚΑ|ΤΕΛΙΚΩΣ|ΤΕΣ|ΤΕΤ|ΤΖΟ|ΤΗ|ΤΗΛ|ΤΗΝ|ΤΗΣ|ΤΙ|ΤΙΚ|ΤΙΜ|ΤΙΠΟΤΑ|ΤΙΠΟΤΕ|ΤΙΣ|ΤΝΤ|ΤΟ|ΤΟΙ|ΤΟΚ|ΤΟΜ|ΤΟΝ|ΤΟΠ|ΤΟΣ|ΤΟΣ?Ν|ΤΟΣΑ|ΤΟΣΕΣ|ΤΟΣΗ|ΤΟΣΗΝ|ΤΟΣΗΣ|ΤΟΣΟ|ΤΟΣΟΙ|ΤΟΣΟΝ|ΤΟΣΟΣ|ΤΟΣΟΥ|ΤΟΣΟΥΣ|ΤΟΤΕ|ΤΟΥ|ΤΟΥΛΑΧΙΣΤΟ|ΤΟΥΛΑΧΙΣΤΟΝ|ΤΟΥΣ|ΤΣ|ΤΣΑ|ΤΣΕ|ΤΥΧΟΝ|ΤΩ|ΤΩΝ|ΤΩΡΑ|ΥΑΣ|ΥΒΑ|ΥΒΟ|ΥΙΕ|ΥΙΟ|ΥΛΑ|ΥΛΗ|ΥΝΙ|ΥΠ|ΥΠΕΡ|ΥΠΟ|ΥΠΟΨΗ|ΥΠΟΨΙΝ|ΥΣΤΕΡΑ|ΥΦΗ|ΥΨΗ|ΦΑ|ΦΑΐ|ΦΑΕ|ΦΑΝ|ΦΑΞ|ΦΑΣ|ΦΑΩ|ΦΕΖ|ΦΕΙ|ΦΕΤΟΣ|ΦΕΥ|ΦΙ|ΦΙΛ|ΦΙΣ|ΦΟΞ|ΦΠΑ|ΦΡΙ|ΧΑ|ΧΑΗ|ΧΑΛ|ΧΑΝ|ΧΑΦ|ΧΕ|ΧΕΙ|ΧΘΕΣ|ΧΙ|ΧΙΑ|ΧΙΛ|ΧΙΟ|ΧΛΜ|ΧΜ|ΧΟΗ|ΧΟΛ|ΧΡΩ|ΧΤΕΣ|ΧΩΡΙΣ|ΧΩΡΙΣΤΑ|ΨΕΣ|ΨΗΛΑ|ΨΙ|ΨΙΤ|Ω|ΩΑ|ΩΑΣ|ΩΔΕ|ΩΕΣ|ΩΘΩ|ΩΜΑ|ΩΜΕ|ΩΝ|ΩΟ|ΩΟΝ|ΩΟΥ|ΩΣ|ΩΣΑΝ|ΩΣΗ|ΩΣΟΤΟΥ|ΩΣΠΟΥ|ΩΣΤΕ|ΩΣΤΟΣΟ|ΩΤΑ|ΩΧ|ΩΩΝ)$/';

		if (preg_match($stop_words, $w))
		{
			return $this->returnStem($w, $w_CASE, $numberOfRulesExamined);
		}

		// step1list is used in Step 1. 41 stems
		$step1list               = Array();
		$step1list["ΦΑΓΙΑ"]      = "ΦΑ";
		$step1list["ΦΑΓΙΟΥ"]     = "ΦΑ";
		$step1list["ΦΑΓΙΩΝ"]     = "ΦΑ";
		$step1list["ΣΚΑΓΙΑ"]     = "ΣΚΑ";
		$step1list["ΣΚΑΓΙΟΥ"]    = "ΣΚΑ";
		$step1list["ΣΚΑΓΙΩΝ"]    = "ΣΚΑ";
		$step1list["ΟΛΟΓΙΟΥ"]    = "ΟΛΟ";
		$step1list["ΟΛΟΓΙΑ"]     = "ΟΛΟ";
		$step1list["ΟΛΟΓΙΩΝ"]    = "ΟΛΟ";
		$step1list["ΣΟΓΙΟΥ"]     = "ΣΟ";
		$step1list["ΣΟΓΙΑ"]      = "ΣΟ";
		$step1list["ΣΟΓΙΩΝ"]     = "ΣΟ";
		$step1list["ΤΑΤΟΓΙΑ"]    = "ΤΑΤΟ";
		$step1list["ΤΑΤΟΓΙΟΥ"]   = "ΤΑΤΟ";
		$step1list["ΤΑΤΟΓΙΩΝ"]   = "ΤΑΤΟ";
		$step1list["ΚΡΕΑΣ"]      = "ΚΡΕ";
		$step1list["ΚΡΕΑΤΟΣ"]    = "ΚΡΕ";
		$step1list["ΚΡΕΑΤΑ"]     = "ΚΡΕ";
		$step1list["ΚΡΕΑΤΩΝ"]    = "ΚΡΕ";
		$step1list["ΠΕΡΑΣ"]      = "ΠΕΡ";
		$step1list["ΠΕΡΑΤΟΣ"]    = "ΠΕΡ";
		$step1list["ΠΕΡΑΤΗ"]     = "ΠΕΡ"; //Added by Spyros . also at $re in step1
		$step1list["ΠΕΡΑΤΑ"]     = "ΠΕΡ";
		$step1list["ΠΕΡΑΤΩΝ"]    = "ΠΕΡ";
		$step1list["ΤΕΡΑΣ"]      = "ΤΕΡ";
		$step1list["ΤΕΡΑΤΟΣ"]    = "ΤΕΡ";
		$step1list["ΤΕΡΑΤΑ"]     = "ΤΕΡ";
		$step1list["ΤΕΡΑΤΩΝ"]    = "ΤΕΡ";
		$step1list["ΦΩΣ"]        = "ΦΩ";
		$step1list["ΦΩΤΟΣ"]      = "ΦΩ";
		$step1list["ΦΩΤΑ"]       = "ΦΩ";
		$step1list["ΦΩΤΩΝ"]      = "ΦΩ";
		$step1list["ΚΑΘΕΣΤΩΣ"]   = "ΚΑΘΕΣΤ";
		$step1list["ΚΑΘΕΣΤΩΤΟΣ"] = "ΚΑΘΕΣΤ";
		$step1list["ΚΑΘΕΣΤΩΤΑ"]  = "ΚΑΘΕΣΤ";
		$step1list["ΚΑΘΕΣΤΩΤΩΝ"] = "ΚΑΘΕΣΤ";
		$step1list["ΓΕΓΟΝΟΣ"]    = "ΓΕΓΟΝ";
		$step1list["ΓΕΓΟΝΟΤΟΣ"]  = "ΓΕΓΟΝ";
		$step1list["ΓΕΓΟΝΟΤΑ"]   = "ΓΕΓΟΝ";
		$step1list["ΓΕΓΟΝΟΤΩΝ"]  = "ΓΕΓΟΝ";

		$v  = '(Α|Ε|Η|Ι|Ο|Υ|Ω)';    // vowel
		$v2 = '(Α|Ε|Η|Ι|Ο|Ω)'; //vowel without Y

		$test1 = true;


		//Step S1. 14 stems
		$numberOfRulesExamined++;
		$re       = '/^(.+?)(ΙΖΑ|ΙΖΕΣ|ΙΖΕ|ΙΖΑΜΕ|ΙΖΑΤΕ|ΙΖΑΝ|ΙΖΑΝΕ|ΙΖΩ|ΙΖΕΙΣ|ΙΖΕΙ|ΙΖΟΥΜΕ|ΙΖΕΤΕ|ΙΖΟΥΝ|ΙΖΟΥΝΕ)$/';
		$exceptS1 = '/^(ΑΝΑΜΠΑ|ΕΜΠΑ|ΕΠΑ|ΞΑΝΑΠΑ|ΠΑ|ΠΕΡΙΠΑ|ΑΘΡΟ|ΣΥΝΑΘΡΟ|ΔΑΝΕ)$/';
		$exceptS2 = '/^(ΜΑΡΚ|ΚΟΡΝ|ΑΜΠΑΡ|ΑΡΡ|ΒΑΘΥΡΙ|ΒΑΡΚ|Β|ΒΟΛΒΟΡ|ΓΚΡ|ΓΛΥΚΟΡ|ΓΛΥΚΥΡ|ΙΜΠ|Λ|ΛΟΥ|ΜΑΡ|Μ|ΠΡ|ΜΠΡ|ΠΟΛΥΡ|Π|Ρ|ΠΙΠΕΡΟΡ)$/';
		if (preg_match($re, $w, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$w      = $stem . $step1list[$suffix];
			if (preg_match($exceptS1, $w))
			{
				$w = $w . 'I';
			}
			if (preg_match($exceptS2, $w))
			{
				$w = $w . 'IΖ';
			}

			return $this->returnStem($w, $w_CASE, $numberOfRulesExamined);
		}

		//Step S2. 7 stems
		$numberOfRulesExamined++;
		$re       = '/^(.+?)(ΩΘΗΚΑ|ΩΘΗΚΕΣ|ΩΘΗΚΕ|ΩΘΗΚΑΜΕ|ΩΘΗΚΑΤΕ|ΩΘΗΚΑΝ|ΩΘΗΚΑΝΕ)$/';
		$exceptS1 = '/^(ΑΛ|ΒΙ|ΕΝ|ΥΨ|ΛΙ|ΖΩ|Σ|Χ)$/';
		if (preg_match($re, $w, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$w      = $stem . $step1list[$suffix];
			$test1  = false;
			if (preg_match($exceptS1, $w))
			{
				$w = $w . 'ΩΝ';
			}

			return $this->returnStem($w, $w_CASE, $numberOfRulesExamined);
		}

		//Step S3. 7 stems
		$numberOfRulesExamined++;
		$re       = '/^(.+?)(ΙΣΑ|ΙΣΕΣ|ΙΣΕ|ΙΣΑΜΕ|ΙΣΑΤΕ|ΙΣΑΝ|ΙΣΑΝΕ)$/';
		$exceptS1 = '/^(ΑΝΑΜΠΑ|ΑΘΡΟ|ΕΜΠΑ|ΕΣΕ|ΕΣΩΚΛΕ|ΕΠΑ|ΞΑΝΑΠΑ|ΕΠΕ|ΠΕΡΙΠΑ|ΑΘΡΟ|ΣΥΝΑΘΡΟ|ΔΑΝΕ|ΚΛΕ|ΧΑΡΤΟΠΑ|ΕΞΑΡΧΑ|ΜΕΤΕΠΕ|ΑΠΟΚΛΕ|ΑΠΕΚΛΕ|ΕΚΛΕ|ΠΕ|ΠΕΡΙΠΑ)$/';
		$exceptS2 = '/^(ΑΝ|ΑΦ|ΓΕ|ΓΙΓΑΝΤΟΑΦ|ΓΚΕ|ΔΗΜΟΚΡΑΤ|ΚΟΜ|ΓΚ|Μ|Π|ΠΟΥΚΑΜ|ΟΛΟ|ΛΑΡ)$/';

		if ($w == "ΙΣΑ")
		{
			$w = "ΙΣ";

			return $w;
		}
		if (preg_match($re, $w, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$w      = $stem . $step1list[$suffix];
			$test1  = false;
			if (preg_match($exceptS1, $w))
			{
				$w = $w . 'Ι';
			}

			return $this->returnStem($w, $w_CASE, $numberOfRulesExamined);
		}


		//Step S4. 7 stems
		$numberOfRulesExamined++;
		$re       = '/^(.+?)(ΙΣΩ|ΙΣΕΙΣ|ΙΣΕΙ|ΙΣΟΥΜΕ|ΙΣΕΤΕ|ΙΣΟΥΝ|ΙΣΟΥΝΕ)$/';
		$exceptS1 = '/^(ΑΝΑΜΠΑ|ΕΜΠΑ|ΕΣΕ|ΕΣΩΚΛΕ|ΕΠΑ|ΞΑΝΑΠΑ|ΕΠΕ|ΠΕΡΙΠΑ|ΑΘΡΟ|ΣΥΝΑΘΡΟ|ΔΑΝΕ|ΚΛΕ|ΧΑΡΤΟΠΑ|ΕΞΑΡΧΑ|ΜΕΤΕΠΕ|ΑΠΟΚΛΕ|ΑΠΕΚΛΕ|ΕΚΛΕ|ΠΕ|ΠΕΡΙΠΑ)$/';

		if (preg_match($re, $w, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$w      = $stem . $step1list[$suffix];
			$test1  = false;
			if (preg_match($exceptS1, $w))
			{
				$w = $w . 'Ι';
			}

			return $this->returnStem($w, $w_CASE, $numberOfRulesExamined);
		}
		//Step S5. 11 stems
		$numberOfRulesExamined++;
		$re       = '/^(.+?)(ΙΣΤΟΣ|ΙΣΤΟΥ|ΙΣΤΟ|ΙΣΤΕ|ΙΣΤΟΙ|ΙΣΤΩΝ|ΙΣΤΟΥΣ|ΙΣΤΗ|ΙΣΤΗΣ|ΙΣΤΑ|ΙΣΤΕΣ)$/';
		$exceptS1 = '/^(Μ|Π|ΑΠ|ΑΡ|ΗΔ|ΚΤ|ΣΚ|ΣΧ|ΥΨ|ΦΑ|ΧΡ|ΧΤ|ΑΚΤ|ΑΟΡ|ΑΣΧ|ΑΤΑ|ΑΧΝ|ΑΧΤ|ΓΕΜ|ΓΥΡ|ΕΜΠ|ΕΥΠ|ΕΧΘ|ΗΦΑ|ΉΦΑ|ΚΑΘ|ΚΑΚ|ΚΥΛ|ΛΥΓ|ΜΑΚ|ΜΕΓ|ΤΑΧ|ΦΙΛ|ΧΩΡ)$/';
		$exceptS2 = '/^(ΔΑΝΕ|ΣΥΝΑΘΡΟ|ΚΛΕ|ΣΕ|ΕΣΩΚΛΕ|ΑΣΕ|ΠΛΕ)$/';
		if (preg_match($re, $w, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$w      = $stem . $step1list[$suffix];
			$test1  = false;
			if (preg_match($exceptS1, $w))
			{
				$w = $w . 'ΙΣΤ';
			}
			if (preg_match($exceptS2, $w))
			{
				$w = $w . 'Ι';
			}

			return $this->returnStem($w, $w_CASE, $numberOfRulesExamined);
		}
		//Step S6. 6 stems
		$numberOfRulesExamined++;
		$re       = '/^(.+?)(ΙΣΜΟ|ΙΣΜΟΙ|ΙΣΜΟΣ|ΙΣΜΟΥ|ΙΣΜΟΥΣ|ΙΣΜΩΝ)$/';
		$exceptS1 = '/^(ΑΓΝΩΣΤΙΚ|ΑΤΟΜΙΚ|ΓΝΩΣΤΙΚ|ΕΘΝΙΚ|ΕΚΛΕΚΤΙΚ|ΣΚΕΠΤΙΚ|ΤΟΠΙΚ)$/';
		$exceptS2 = '/^(ΣΕ|ΜΕΤΑΣΕ|ΜΙΚΡΟΣΕ|ΕΓΚΛΕ|ΑΠΟΚΛΕ)$/';
		$exceptS3 = '/^(ΔΑΝΕ|ΑΝΤΙΔΑΝΕ)$/';
		$exceptS4 = '/^(ΑΛΕΞΑΝΔΡΙΝ|ΒΥΖΑΝΤΙΝ|ΘΕΑΤΡΙΝ)$/';
		if (preg_match($re, $w, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$w      = $stem;
			if (preg_match($exceptS1, $w))
			{
				$w = str_replace('ΙΚ', "", $w);
			}
			if (preg_match($exceptS2, $w))
			{
				$w = $w . "ΙΣΜ";
			}
			if (preg_match($exceptS3, $w))
			{
				$w = $w . "Ι";
			}
			if (preg_match($exceptS4, $w))
			{
				$w = str_replace('ΙΝ', "", $w);
			}

			return $this->returnStem($w, $w_CASE, $numberOfRulesExamined);
		}

		//Step S7. 4 stems
		$numberOfRulesExamined++;
		$re       = '/^(.+?)(ΑΡΑΚΙ|ΑΡΑΚΙΑ|ΟΥΔΑΚΙ|ΟΥΔΑΚΙΑ)$/';
		$exceptS1 = '/^(Σ|Χ)$/';
		if (preg_match($re, $w, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$w      = $stem;
			if (preg_match($exceptS1, $w))
			{
				$w = $w . "AΡΑΚ";
			}

			return $this->returnStem($w, $w_CASE, $numberOfRulesExamined);
		}


		//Step S8. 8 stems
		$numberOfRulesExamined++;
		$re       = '/^(.+?)(ΑΚΙ|ΑΚΙΑ|ΙΤΣΑ|ΙΤΣΑΣ|ΙΤΣΕΣ|ΙΤΣΩΝ|ΑΡΑΚΙ|ΑΡΑΚΙΑ)$/';
		$exceptS1 = '/^(ΑΝΘΡ|ΒΑΜΒ|ΒΡ|ΚΑΙΜ|ΚΟΝ|ΚΟΡ|ΛΑΒΡ|ΛΟΥΛ|ΜΕΡ|ΜΟΥΣΤ|ΝΑΓΚΑΣ|ΠΛ|Ρ|ΡΥ|Σ|ΣΚ|ΣΟΚ|ΣΠΑΝ|ΤΖ|ΦΑΡΜ|Χ|ΚΑΠΑΚ|ΑΛΙΣΦ|ΑΜΒΡ|ΑΝΘΡ|Κ|ΦΥΛ|ΚΑΤΡΑΠ|ΚΛΙΜ|ΜΑΛ|ΣΛΟΒ|Φ|ΣΦ|ΤΣΕΧΟΣΛΟΒ)$/';
		$exceptS2 = '/^(Β|ΒΑΛ|ΓΙΑΝ|ΓΛ|Ζ|ΗΓΟΥΜΕΝ|ΚΑΡΔ|ΚΟΝ|ΜΑΚΡΥΝ|ΝΥΦ|ΠΑΤΕΡ|Π|ΣΚ|ΤΟΣ|ΤΡΙΠΟΛ)$/';
		$exceptS3 = '/(ΚΟΡ)$/';// for words like ΠΛΟΥΣΙΟΚΟΡΙΤΣΑ, ΠΑΛΙΟΚΟΡΙΤΣΑ etc
		if (preg_match($re, $w, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$w      = $stem;
			if (preg_match($exceptS1, $w))
			{
				$w = $w . "ΑΚ";
			}
			if (preg_match($exceptS2, $w))
			{
				$w = $w . "ΙΤΣ";
			}
			if (preg_match($exceptS3, $w))
			{
				$w = $w . "ΙΤΣ";
			}

			return $this->returnStem($w, $w_CASE, $numberOfRulesExamined);
		}

		//Step S9. 3 stems
		$numberOfRulesExamined++;
		$re       = '/^(.+?)(ΙΔΙΟ|ΙΔΙΑ|ΙΔΙΩΝ)$/';
		$exceptS1 = '/^(ΑΙΦΝ|ΙΡ|ΟΛΟ|ΨΑΛ)$/';
		$exceptS2 = '/(Ε|ΠΑΙΧΝ)$/';
		if (preg_match($re, $w, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$w      = $stem;
			if (preg_match($exceptS1, $w))
			{
				$w = $w . "ΙΔ";
			}
			if (preg_match($exceptS2, $w))
			{
				$w = $w . "ΙΔ";
			}

			return $this->returnStem($w, $w_CASE, $numberOfRulesExamined);
		}


		//Step S10. 4 stems
		$numberOfRulesExamined++;
		$re       = '/^(.+?)(ΙΣΚΟΣ|ΙΣΚΟΥ|ΙΣΚΟ|ΙΣΚΕ)$/';
		$exceptS1 = '/^(Δ|ΙΒ|ΜΗΝ|Ρ|ΦΡΑΓΚ|ΛΥΚ|ΟΒΕΛ)$/';
		if (preg_match($re, $w, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$w      = $stem;
			if (preg_match($exceptS1, $w))
			{
				$w = $w . "ΙΣΚ";
			}

			return $this->returnStem($w, $w_CASE, $numberOfRulesExamined);
		}


		//Step1
		$numberOfRulesExamined++;
		$re = '/(.*)(ΦΑΓΙΑ|ΦΑΓΙΟΥ|ΦΑΓΙΩΝ|ΣΚΑΓΙΑ|ΣΚΑΓΙΟΥ|ΣΚΑΓΙΩΝ|ΟΛΟΓΙΟΥ|ΟΛΟΓΙΑ|ΟΛΟΓΙΩΝ|ΣΟΓΙΟΥ|ΣΟΓΙΑ|ΣΟΓΙΩΝ|ΤΑΤΟΓΙΑ|ΤΑΤΟΓΙΟΥ|ΤΑΤΟΓΙΩΝ|ΚΡΕΑΣ|ΚΡΕΑΤΟΣ|ΚΡΕΑΤΑ|ΚΡΕΑΤΩΝ|ΠΕΡΑΣ|ΠΕΡΑΤΟΣ|ΠΕΡΑΤΗ|ΠΕΡΑΤΑ|ΠΕΡΑΤΩΝ|ΤΕΡΑΣ|ΤΕΡΑΤΟΣ|ΤΕΡΑΤΑ|ΤΕΡΑΤΩΝ|ΦΩΣ|ΦΩΤΟΣ|ΦΩΤΑ|ΦΩΤΩΝ|ΚΑΘΕΣΤΩΣ|ΚΑΘΕΣΤΩΤΟΣ|ΚΑΘΕΣΤΩΤΑ|ΚΑΘΕΣΤΩΤΩΝ|ΓΕΓΟΝΟΣ|ΓΕΓΟΝΟΤΟΣ|ΓΕΓΟΝΟΤΑ|ΓΕΓΟΝΟΤΩΝ)$/';


		if (preg_match($re, $w, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$w      = $stem . $step1list[$suffix];
			$test1  = false;

		}


		// Step 2a. 2 stems
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ΑΔΕΣ|ΑΔΩΝ)$/';
		if (preg_match($re, $w, $match))
		{
			$stem = $match[1];
			$w    = $stem;
			$re   = '/(ΟΚ|ΜΑΜ|ΜΑΝ|ΜΠΑΜΠ|ΠΑΤΕΡ|ΓΙΑΓΙ|ΝΤΑΝΤ|ΚΥΡ|ΘΕΙ|ΠΕΘΕΡ)$/';
			if (!preg_match($re, $w))
			{
				$w = $w . "ΑΔ";
			}


		}

		//Step 2b. 2 stems
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ΕΔΕΣ|ΕΔΩΝ)$/';
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem   = $match[1];
			$w      = $stem;
			$exept2 = '/(ΟΠ|ΙΠ|ΕΜΠ|ΥΠ|ΓΗΠ|ΔΑΠ|ΚΡΑΣΠ|ΜΙΛ)$/';
			if (preg_match($exept2, $w))
			{
				$w = $w . 'ΕΔ';
			}

		}

		//Step 2c
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ΟΥΔΕΣ|ΟΥΔΩΝ)$/';
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem = $match[1];
			$w    = $stem;

			$exept3 = '/(ΑΡΚ|ΚΑΛΙΑΚ|ΠΕΤΑΛ|ΛΙΧ|ΠΛΕΞ|ΣΚ|Σ|ΦΛ|ΦΡ|ΒΕΛ|ΛΟΥΛ|ΧΝ|ΣΠ|ΤΡΑΓ|ΦΕ)$/';
			if (preg_match($exept3, $w))
			{
				$w = $w . 'ΟΥΔ';
			}

		}

		//Step 2d
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ΕΩΣ|ΕΩΝ)$/';
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$exept4 = '/^(Θ|Δ|ΕΛ|ΓΑΛ|Ν|Π|ΙΔ|ΠΑΡ)$/';
			if (preg_match($exept4, $w))
			{
				$w = $w . 'Ε';
			}

		}

		//Step 3
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ΙΑ|ΙΟΥ|ΙΩΝ)$/';
		if (preg_match($re, $w, $fp))
		{
			$stem  = $fp[1];
			$w     = $stem;
			$re    = '/' . $v . '$/';
			$test1 = false;
			if (preg_match($re, $w))
			{
				$w = $stem . 'Ι';
			}
		}

		//Step 4
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ΙΚΑ|ΙΚΟ|ΙΚΟΥ|ΙΚΩΝ)$/';
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem = $match[1];
			$w    = $stem;

			$test1  = false;
			$re     = '/' . $v . '$/';
			$exept5 = '/^(ΑΛ|ΑΔ|ΕΝΔ|ΑΜΑΝ|ΑΜΜΟΧΑΛ|ΗΘ|ΑΝΗΘ|ΑΝΤΙΔ|ΦΥΣ|ΒΡΩΜ|ΓΕΡ|ΕΞΩΔ|ΚΑΛΠ|ΚΑΛΛΙΝ|ΚΑΤΑΔ|ΜΟΥΛ|ΜΠΑΝ|ΜΠΑΓΙΑΤ|ΜΠΟΛ|ΜΠΟΣ|ΝΙΤ|ΞΙΚ|ΣΥΝΟΜΗΛ|ΠΕΤΣ|ΠΙΤΣ|ΠΙΚΑΝΤ|ΠΛΙΑΤΣ|ΠΟΣΤΕΛΝ|ΠΡΩΤΟΔ|ΣΕΡΤ|ΣΥΝΑΔ|ΤΣΑΜ|ΥΠΟΔ|ΦΙΛΟΝ|ΦΥΛΟΔ|ΧΑΣ)$/';
			if (preg_match($re, $w) || preg_match($exept5, $w))
			{
				$w = $w . 'ΙΚ';
			}
		}

		//step 5a
		$numberOfRulesExamined++;
		$re  = '/^(.+?)(ΑΜΕ)$/';
		$re2 = '/^(.+?)(ΑΓΑΜΕ|ΗΣΑΜΕ|ΟΥΣΑΜΕ|ΗΚΑΜΕ|ΗΘΗΚΑΜΕ)$/';
		if ($w == "ΑΓΑΜΕ")
		{
			$w = "ΑΓΑΜ";

		}

		if (preg_match($re2, $w))
		{
			preg_match($re2, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;
		}
		$numberOfRulesExamined++;
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$exept6 = '/^(ΑΝΑΠ|ΑΠΟΘ|ΑΠΟΚ|ΑΠΟΣΤ|ΒΟΥΒ|ΞΕΘ|ΟΥΛ|ΠΕΘ|ΠΙΚΡ|ΠΟΤ|ΣΙΧ|Χ)$/';
			if (preg_match($exept6, $w))
			{
				$w = $w . "ΑΜ";
			}
		}

		//Step 5b
		$numberOfRulesExamined++;
		$re2 = '/^(.+?)(ΑΝΕ)$/';
		$re3 = '/^(.+?)(ΑΓΑΝΕ|ΗΣΑΝΕ|ΟΥΣΑΝΕ|ΙΟΝΤΑΝΕ|ΙΟΤΑΝΕ|ΙΟΥΝΤΑΝΕ|ΟΝΤΑΝΕ|ΟΤΑΝΕ|ΟΥΝΤΑΝΕ|ΗΚΑΝΕ|ΗΘΗΚΑΝΕ)$/';

		if (preg_match($re3, $w))
		{
			preg_match($re3, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$re3 = '/^(ΤΡ|ΤΣ)$/';
			if (preg_match($re3, $w))
			{
				$w = $w . "ΑΓΑΝ";
			}
		}
		$numberOfRulesExamined++;
		if (preg_match($re2, $w))
		{
			preg_match($re2, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$re2    = '/' . $v2 . '$/';
			$exept7 = '/^(ΒΕΤΕΡ|ΒΟΥΛΚ|ΒΡΑΧΜ|Γ|ΔΡΑΔΟΥΜ|Θ|ΚΑΛΠΟΥΖ|ΚΑΣΤΕΛ|ΚΟΡΜΟΡ|ΛΑΟΠΛ|ΜΩΑΜΕΘ|Μ|ΜΟΥΣΟΥΛΜ|Ν|ΟΥΛ|Π|ΠΕΛΕΚ|ΠΛ|ΠΟΛΙΣ|ΠΟΡΤΟΛ|ΣΑΡΑΚΑΤΣ|ΣΟΥΛΤ|ΤΣΑΡΛΑΤ|ΟΡΦ|ΤΣΙΓΓ|ΤΣΟΠ|ΦΩΤΟΣΤΕΦ|Χ|ΨΥΧΟΠΛ|ΑΓ|ΟΡΦ|ΓΑΛ|ΓΕΡ|ΔΕΚ|ΔΙΠΛ|ΑΜΕΡΙΚΑΝ|ΟΥΡ|ΠΙΘ|ΠΟΥΡΙΤ|Σ|ΖΩΝΤ|ΙΚ|ΚΑΣΤ|ΚΟΠ|ΛΙΧ|ΛΟΥΘΗΡ|ΜΑΙΝΤ|ΜΕΛ|ΣΙΓ|ΣΠ|ΣΤΕΓ|ΤΡΑΓ|ΤΣΑΓ|Φ|ΕΡ|ΑΔΑΠ|ΑΘΙΓΓ|ΑΜΗΧ|ΑΝΙΚ|ΑΝΟΡΓ|ΑΠΗΓ|ΑΠΙΘ|ΑΤΣΙΓΓ|ΒΑΣ|ΒΑΣΚ|ΒΑΘΥΓΑΛ|ΒΙΟΜΗΧ|ΒΡΑΧΥΚ|ΔΙΑΤ|ΔΙΑΦ|ΕΝΟΡΓ|ΘΥΣ|ΚΑΠΝΟΒΙΟΜΗΧ|ΚΑΤΑΓΑΛ|ΚΛΙΒ|ΚΟΙΛΑΡΦ|ΛΙΒ|ΜΕΓΛΟΒΙΟΜΗΧ|ΜΙΚΡΟΒΙΟΜΗΧ|ΝΤΑΒ|ΞΗΡΟΚΛΙΒ|ΟΛΙΓΟΔΑΜ|ΟΛΟΓΑΛ|ΠΕΝΤΑΡΦ|ΠΕΡΗΦ|ΠΕΡΙΤΡ|ΠΛΑΤ|ΠΟΛΥΔΑΠ|ΠΟΛΥΜΗΧ|ΣΤΕΦ|ΤΑΒ|ΤΕΤ|ΥΠΕΡΗΦ|ΥΠΟΚΟΠ|ΧΑΜΗΛΟΔΑΠ|ΨΗΛΟΤΑΒ)$/';
			if (preg_match($re2, $w) || preg_match($exept7, $w))
			{
				$w = $w . "ΑΝ";
			}
		}

		//Step 5c
		$numberOfRulesExamined++;
		$re3 = '/^(.+?)(ΕΤΕ)$/';
		$re4 = '/^(.+?)(ΗΣΕΤΕ)$/';

		if (preg_match($re4, $w))
		{
			preg_match($re4, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;
		}
		$numberOfRulesExamined++;
		if (preg_match($re3, $w))
		{
			preg_match($re3, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$re3    = '/' . $v2 . '$/';
			$exept8 = '/(ΟΔ|ΑΙΡ|ΦΟΡ|ΤΑΘ|ΔΙΑΘ|ΣΧ|ΕΝΔ|ΕΥΡ|ΤΙΘ|ΥΠΕΡΘ|ΡΑΘ|ΕΝΘ|ΡΟΘ|ΣΘ|ΠΥΡ|ΑΙΝ|ΣΥΝΔ|ΣΥΝ|ΣΥΝΘ|ΧΩΡ|ΠΟΝ|ΒΡ|ΚΑΘ|ΕΥΘ|ΕΚΘ|ΝΕΤ|ΡΟΝ|ΑΡΚ|ΒΑΡ|ΒΟΛ|ΩΦΕΛ)$/';
			$exept9 = '/^(ΑΒΑΡ|ΒΕΝ|ΕΝΑΡ|ΑΒΡ|ΑΔ|ΑΘ|ΑΝ|ΑΠΛ|ΒΑΡΟΝ|ΝΤΡ|ΣΚ|ΚΟΠ|ΜΠΟΡ|ΝΙΦ|ΠΑΓ|ΠΑΡΑΚΑΛ|ΣΕΡΠ|ΣΚΕΛ|ΣΥΡΦ|ΤΟΚ|Υ|Δ|ΕΜ|ΘΑΡΡ|Θ)$/';

			if (preg_match($re3, $w) || preg_match($exept8, $w) || preg_match($exept9, $w))
			{
				$w = $w . "ΕΤ";
			}
		}

		//Step 5d
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ΟΝΤΑΣ|ΩΝΤΑΣ)$/';
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$exept10 = '/^(ΑΡΧ)$/';
			$exept11 = '/(ΚΡΕ)$/';
			if (preg_match($exept10, $w))
			{
				$w = $w . "ΟΝΤ";
			}
			if (preg_match($exept11, $w))
			{
				$w = $w . "ΩΝΤ";
			}
		}

		//Step 5e
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ΟΜΑΣΤΕ|ΙΟΜΑΣΤΕ)$/';
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$exept11 = '/^(ΟΝ)$/';
			if (preg_match($exept11, $w))
			{
				$w = $w . "ΟΜΑΣΤ";
			}
		}

		//Step 5f
		$numberOfRulesExamined++;
		$re  = '/^(.+?)(ΕΣΤΕ)$/';
		$re2 = '/^(.+?)(ΙΕΣΤΕ)$/';

		if (preg_match($re2, $w))
		{
			preg_match($re2, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$re2 = '/^(Π|ΑΠ|ΣΥΜΠ|ΑΣΥΜΠ|ΑΚΑΤΑΠ|ΑΜΕΤΑΜΦ)$/';
			if (preg_match($re2, $w))
			{
				$w = $w . "ΙΕΣΤ";
			}
		}
		$numberOfRulesExamined++;
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$exept12 = '/^(ΑΛ|ΑΡ|ΕΚΤΕΛ|Ζ|Μ|Ξ|ΠΑΡΑΚΑΛ|ΑΡ|ΠΡΟ|ΝΙΣ)$/';
			if (preg_match($exept12, $w))
			{
				$w = $w . "ΕΣΤ";
			}
		}

		//Step 5g
		$numberOfRulesExamined++;
		$re  = '/^(.+?)(ΗΚΑ|ΗΚΕΣ|ΗΚΕ)$/';
		$re2 = '/^(.+?)(ΗΘΗΚΑ|ΗΘΗΚΕΣ|ΗΘΗΚΕ)$/';

		if (preg_match($re2, $w))
		{
			preg_match($re2, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;
		}
		$numberOfRulesExamined++;
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$exept13 = '/(ΣΚΩΛ|ΣΚΟΥΛ|ΝΑΡΘ|ΣΦ|ΟΘ|ΠΙΘ)$/';
			$exept14 = '/^(ΔΙΑΘ|Θ|ΠΑΡΑΚΑΤΑΘ|ΠΡΟΣΘ|ΣΥΝΘ|)$/';
			if (preg_match($exept13, $w) || preg_match($exept14, $w))
			{
				$w = $w . "ΗΚ";
			}
		}


		//Step 5h
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ΟΥΣΑ|ΟΥΣΕΣ|ΟΥΣΕ)$/';
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$exept15 = '/^(ΦΑΡΜΑΚ|ΧΑΔ|ΑΓΚ|ΑΝΑΡΡ|ΒΡΟΜ|ΕΚΛΙΠ|ΛΑΜΠΙΔ|ΛΕΧ|Μ|ΠΑΤ|Ρ|Λ|ΜΕΔ|ΜΕΣΑΖ|ΥΠΟΤΕΙΝ|ΑΜ|ΑΙΘ|ΑΝΗΚ|ΔΕΣΠΟΖ|ΕΝΔΙΑΦΕΡ|ΔΕ|ΔΕΥΤΕΡΕΥ|ΚΑΘΑΡΕΥ|ΠΛΕ|ΤΣΑ)$/';
			$exept16 = '/(ΠΟΔΑΡ|ΒΛΕΠ|ΠΑΝΤΑΧ|ΦΡΥΔ|ΜΑΝΤΙΛ|ΜΑΛΛ|ΚΥΜΑΤ|ΛΑΧ|ΛΗΓ|ΦΑΓ|ΟΜ|ΠΡΩΤ)$/';
			if (preg_match($exept15, $w) || preg_match($exept16, $w))
			{
				$w = $w . "ΟΥΣ";
			}
		}

		//Step 5i
		$re = '/^(.+?)(ΑΓΑ|ΑΓΕΣ|ΑΓΕ)$/';
		$numberOfRulesExamined++;
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$exept17 = '/^(ΨΟΦ|ΝΑΥΛΟΧ)$/';
			$exept20 = '/(ΚΟΛΛ)$/';
			$exept18 = '/^(ΑΒΑΣΤ|ΠΟΛΥΦ|ΑΔΗΦ|ΠΑΜΦ|Ρ|ΑΣΠ|ΑΦ|ΑΜΑΛ|ΑΜΑΛΛΙ|ΑΝΥΣΤ|ΑΠΕΡ|ΑΣΠΑΡ|ΑΧΑΡ|ΔΕΡΒΕΝ|ΔΡΟΣΟΠ|ΞΕΦ|ΝΕΟΠ|ΝΟΜΟΤ|ΟΛΟΠ|ΟΜΟΤ|ΠΡΟΣΤ|ΠΡΟΣΩΠΟΠ|ΣΥΜΠ|ΣΥΝΤ|Τ|ΥΠΟΤ|ΧΑΡ|ΑΕΙΠ|ΑΙΜΟΣΤ|ΑΝΥΠ|ΑΠΟΤ|ΑΡΤΙΠ|ΔΙΑΤ|ΕΝ|ΕΠΙΤ|ΚΡΟΚΑΛΟΠ|ΣΙΔΗΡΟΠ|Λ|ΝΑΥ|ΟΥΛΑΜ|ΟΥΡ|Π|ΤΡ|Μ)$/';
			$exept19 = '/(ΟΦ|ΠΕΛ|ΧΟΡΤ|ΛΛ|ΣΦ|ΡΠ|ΦΡ|ΠΡ|ΛΟΧ|ΣΜΗΝ)$/';

			if ((preg_match($exept18, $w) || preg_match($exept19, $w))
				&& !(preg_match($exept17, $w) || preg_match($exept20, $w)))
			{
				$w = $w . "ΑΓ";
			}
		}


		//Step 5j
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ΗΣΕ|ΗΣΟΥ|ΗΣΑ)$/';
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$exept21 = '/^(Ν|ΧΕΡΣΟΝ|ΔΩΔΕΚΑΝ|ΕΡΗΜΟΝ|ΜΕΓΑΛΟΝ|ΕΠΤΑΝ)$/';
			if (preg_match($exept21, $w))
			{
				$w = $w . "ΗΣ";
			}
		}

		//Step 5k
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ΗΣΤΕ)$/';
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$exept22 = '/^(ΑΣΒ|ΣΒ|ΑΧΡ|ΧΡ|ΑΠΛ|ΑΕΙΜΝ|ΔΥΣΧΡ|ΕΥΧΡ|ΚΟΙΝΟΧΡ|ΠΑΛΙΜΨ)$/';
			if (preg_match($exept22, $w))
			{
				$w = $w . "ΗΣΤ";
			}
		}

		//Step 5l
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ΟΥΝΕ|ΗΣΟΥΝΕ|ΗΘΟΥΝΕ)$/';
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$exept23 = '/^(Ν|Ρ|ΣΠΙ|ΣΤΡΑΒΟΜΟΥΤΣ|ΚΑΚΟΜΟΥΤΣ|ΕΞΩΝ)$/';
			if (preg_match($exept23, $w))
			{
				$w = $w . "ΟΥΝ";
			}
		}

		//Step 5l
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ΟΥΜΕ|ΗΣΟΥΜΕ|ΗΘΟΥΜΕ)$/';
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem  = $match[1];
			$w     = $stem;
			$test1 = false;

			$exept24 = '/^(ΠΑΡΑΣΟΥΣ|Φ|Χ|ΩΡΙΟΠΛ|ΑΖ|ΑΛΛΟΣΟΥΣ|ΑΣΟΥΣ)$/';
			if (preg_match($exept24, $w))
			{
				$w = $w . "ΟΥΜ";
			}
		}

		// Step 6
		$numberOfRulesExamined++;
		$re  = '/^(.+?)(ΜΑΤΑ|ΜΑΤΩΝ|ΜΑΤΟΣ)$/';
		$re2 = '/^(.+?)(Α|ΑΓΑΤΕ|ΑΓΑΝ|ΑΕΙ|ΑΜΑΙ|ΑΝ|ΑΣ|ΑΣΑΙ|ΑΤΑΙ|ΑΩ|Ε|ΕΙ|ΕΙΣ|ΕΙΤΕ|ΕΣΑΙ|ΕΣ|ΕΤΑΙ|Ι|ΙΕΜΑΙ|ΙΕΜΑΣΤΕ|ΙΕΤΑΙ|ΙΕΣΑΙ|ΙΕΣΑΣΤΕ|ΙΟΜΑΣΤΑΝ|ΙΟΜΟΥΝ|ΙΟΜΟΥΝΑ|ΙΟΝΤΑΝ|ΙΟΝΤΟΥΣΑΝ|ΙΟΣΑΣΤΑΝ|ΙΟΣΑΣΤΕ|ΙΟΣΟΥΝ|ΙΟΣΟΥΝΑ|ΙΟΤΑΝ|ΙΟΥΜΑ|ΙΟΥΜΑΣΤΕ|ΙΟΥΝΤΑΙ|ΙΟΥΝΤΑΝ|Η|ΗΔΕΣ|ΗΔΩΝ|ΗΘΕΙ|ΗΘΕΙΣ|ΗΘΕΙΤΕ|ΗΘΗΚΑΤΕ|ΗΘΗΚΑΝ|ΗΘΟΥΝ|ΗΘΩ|ΗΚΑΤΕ|ΗΚΑΝ|ΗΣ|ΗΣΑΝ|ΗΣΑΤΕ|ΗΣΕΙ|ΗΣΕΣ|ΗΣΟΥΝ|ΗΣΩ|Ο|ΟΙ|ΟΜΑΙ|ΟΜΑΣΤΑΝ|ΟΜΟΥΝ|ΟΜΟΥΝΑ|ΟΝΤΑΙ|ΟΝΤΑΝ|ΟΝΤΟΥΣΑΝ|ΟΣ|ΟΣΑΣΤΑΝ|ΟΣΑΣΤΕ|ΟΣΟΥΝ|ΟΣΟΥΝΑ|ΟΤΑΝ|ΟΥ|ΟΥΜΑΙ|ΟΥΜΑΣΤΕ|ΟΥΝ|ΟΥΝΤΑΙ|ΟΥΝΤΑΝ|ΟΥΣ|ΟΥΣΑΝ|ΟΥΣΑΤΕ|Υ|ΥΣ|Ω|ΩΝ)$/';
		if (preg_match($re, $w, $match))
		{
			$stem = $match[1];
			$w    = $stem . "ΜΑ";
		}
		$numberOfRulesExamined++;
		if (preg_match($re2, $w) && $test1)
		{
			preg_match($re2, $w, $match);
			$stem = $match[1];
			$w    = $stem;
		}

		// Step 7 (ΠΑΡΑΘΕΤΙΚΑ)
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ΕΣΤΕΡ|ΕΣΤΑΤ|ΟΤΕΡ|ΟΤΑΤ|ΥΤΕΡ|ΥΤΑΤ|ΩΤΕΡ|ΩΤΑΤ)$/';
		if (preg_match($re, $w))
		{
			preg_match($re, $w, $match);
			$stem = $match[1];
			$w    = $stem;
		}

		return $this->returnStem($w, $w_CASE, $numberOfRulesExamined);
	}

	protected function returnStem($w, $w_CASE, $numberOfRulesExamined)
	{
		//convert case back to initial by reading $w_CASE
		$unacceptedLetters = array(
			"α",
			"β",
			"γ",
			"δ",
			"ε",
			"ζ",
			"η",
			"θ",
			"ι",
			"κ",
			"λ",
			"μ",
			"ν",
			"ξ",
			"ο",
			"π",
			"ρ",
			"σ",
			"τ",
			"υ",
			"φ",
			"χ",
			"ψ",
			"ω",
			"ά",
			"έ",
			"ή",
			"ί",
			"ό",
			"ύ",
			"ς",
			"ώ",
			"ϊ",
		);
		$acceptedLetters   = array(
			"Α",
			"Β",
			"Γ",
			"Δ",
			"Ε",
			"Ζ",
			"Η",
			"Θ",
			"Ι",
			"Κ",
			"Λ",
			"Μ",
			"Ν",
			"Ξ",
			"Ο",
			"Π",
			"Ρ",
			"Σ",
			"Τ",
			"Υ",
			"Φ",
			"Χ",
			"Ψ",
			"Ω",
			"Α",
			"Ε",
			"Η",
			"Ι",
			"Ο",
			"Υ",
			"Σ",
			"Ω",
			"Ι",
		);
		for ($i = 0; $i <= mb_strlen($w, 'UTF-8') - 1; $i++)
		{
			if (@$w_CASE[$i] == 1)
			{
				for ($k = 0; $k <= 32; $k = $k + 1)
				{
					if ($w[$i] == $acceptedLetters[$k])
					{
						$w[$i] = $unacceptedLetters[$k];
					}
				}
			}
			elseif (@$w_CASE[$i] == 2)
			{
				$w[$i] = "ς";
			}
		}

		$returnResults    = array();
		$returnResults[0] = $w;
		$returnResults[1] = $numberOfRulesExamined;

		return $returnResults;
	}
}