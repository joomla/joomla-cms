<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 *
 * The Greek stemmer was adapted for Joomla! 4 by Nicholas K. Dionysopoulos <nicholas@akeebabackup.com>. This is
 * derivative work, based on the Greek stemmer for Drupal, see https://www.drupal.org/project/greekstemmer The following
 * copyright notices are included in the original source.
 *
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
		/**
		 * Case conversion map. 1 for changed case in that position, 2 for final sigma.
		 *
		 * We need to go through this manual case conversion because both final sigma (ς) and lowercase sigma (σ) get
		 * converted to uppercase sigma (Σ) making the inverse conversion impossible if you just lower/uppercase each
		 * letter position. Furthermore, the PHP functions for converting string case work on the entire word, not
		 * individual characters. This means that CamelCased Greek, such as ΜικτόΒάρος or κάτιΝέο would be converted
		 * to the wrong case if we were to use strtolower().
		 */
		$w_CASE = array_fill(0, mb_strlen($token, 'UTF-8'), 0);

		// First we must find all letters that are not in Upper case and store their position
		$lowerCaseLetters = array(
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
			// Double accented letters (διαλυτικά and τόνος), added by Nicholas
			"ΐ",
			"ΰ",
		);
		$upperCaseLetters = array(
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
			// Double accented letters (διαλυτικά and τόνος), added by Nicholas
			"Ι",
			"Υ",
		);

		for ($k = 0; $k <= 32; $k = $k + 1)
		{
			for ($i = 0; $i <= mb_strlen($token, 'UTF-8') - 1; $i++)
			{
				if ($token[$i] == "ς")
				{
					$token[$i]  = "Σ";
					$w_CASE[$i] = 2;

					continue;
				}

				if ($token[$i] == $lowerCaseLetters[$k])
				{
					$token[$i]  = $upperCaseLetters[$k];
					$w_CASE[$i] = "1";
				}
			}
		}

		// Stop-word removal
		$stop_words = '/^(ΕΚΟ|ΑΒΑ|ΑΓΑ|ΑΓΗ|ΑΓΩ|ΑΔΗ|ΑΔΩ|ΑΕ|ΑΕΙ|ΑΘΩ|ΑΙ|ΑΙΚ|ΑΚΗ|ΑΚΟΜΑ|ΑΚΟΜΗ|ΑΚΡΙΒΩΣ|ΑΛΑ|ΑΛΗΘΕΙΑ|ΑΛΗΘΙΝΑ|ΑΛΛΑΧΟΥ|ΑΛΛΙΩΣ|ΑΛΛΙΩΤΙΚΑ|ΑΛΛΟΙΩΣ|ΑΛΛΟΙΩΤΙΚΑ|ΑΛΛΟΤΕ|ΑΛΤ|ΑΛΩ|ΑΜΑ|ΑΜΕ|ΑΜΕΣΑ|ΑΜΕΣΩΣ|ΑΜΩ|ΑΝ|ΑΝΑ|ΑΝΑΜΕΣΑ|ΑΝΑΜΕΤΑΞΥ|ΑΝΕΥ|ΑΝΤΙ|ΑΝΤΙΠΕΡΑ|ΑΝΤΙΣ|ΑΝΩ|ΑΝΩΤΕΡΩ|ΑΞΑΦΝΑ|ΑΠ|ΑΠΕΝΑΝΤΙ|ΑΠΟ|ΑΠΟΨΕ|ΑΠΩ|ΑΡΑ|ΑΡΑΓΕ|ΑΡΕ|ΑΡΚ|ΑΡΚΕΤΑ|ΑΡΛ|ΑΡΜ|ΑΡΤ|ΑΡΥ|ΑΡΩ|ΑΣ|ΑΣΑ|ΑΣΟ|ΑΤΑ|ΑΤΕ|ΑΤΗ|ΑΤΙ|ΑΤΜ|ΑΤΟ|ΑΥΡΙΟ|ΑΦΗ|ΑΦΟΤΟΥ|ΑΦΟΥ|ΑΧ|ΑΧΕ|ΑΧΟ|ΑΨΑ|ΑΨΕ|ΑΨΗ|ΑΨΥ|ΑΩΕ|ΑΩΟ|ΒΑΝ|ΒΑΤ|ΒΑΧ|ΒΕΑ|ΒΕΒΑΙΟΤΑΤΑ|ΒΗΞ|ΒΙΑ|ΒΙΕ|ΒΙΗ|ΒΙΟ|ΒΟΗ|ΒΟΩ|ΒΡΕ|ΓΑ|ΓΑΒ|ΓΑΡ|ΓΕΝ|ΓΕΣ||ΓΗ|ΓΗΝ|ΓΙ|ΓΙΑ|ΓΙΕ|ΓΙΝ|ΓΙΟ|ΓΚΙ|ΓΙΑΤΙ|ΓΚΥ|ΓΟΗ|ΓΟΟ|ΓΡΗΓΟΡΑ|ΓΡΙ|ΓΡΥ|ΓΥΗ|ΓΥΡΩ|ΔΑ|ΔΕ|ΔΕΗ|ΔΕΙ|ΔΕΝ|ΔΕΣ|ΔΗ|ΔΗΘΕΝ|ΔΗΛΑΔΗ|ΔΗΩ|ΔΙ|ΔΙΑ|ΔΙΑΡΚΩΣ|ΔΙΟΛΟΥ|ΔΙΣ|ΔΙΧΩΣ|ΔΟΛ|ΔΟΝ|ΔΡΑ|ΔΡΥ|ΔΡΧ|ΔΥΕ|ΔΥΟ|ΔΩ|ΕΑΜ|ΕΑΝ|ΕΑΡ|ΕΘΗ|ΕΙ|ΕΙΔΕΜΗ|ΕΙΘΕ|ΕΙΜΑΙ|ΕΙΜΑΣΤΕ|ΕΙΝΑΙ|ΕΙΣ|ΕΙΣΑΙ|ΕΙΣΑΣΤΕ|ΕΙΣΤΕ|ΕΙΤΕ|ΕΙΧΑ|ΕΙΧΑΜΕ|ΕΙΧΑΝ|ΕΙΧΑΤΕ|ΕΙΧΕ|ΕΙΧΕΣ|ΕΚ|ΕΚΕΙ|ΕΛΑ|ΕΛΙ|ΕΜΠ|ΕΝ|ΕΝΤΕΛΩΣ|ΕΝΤΟΣ|ΕΝΤΩΜΕΤΑΞΥ|ΕΝΩ|ΕΞ|ΕΞΑΦΝΑ|ΕΞΙ|ΕΞΙΣΟΥ|ΕΞΩ|ΕΟΚ|ΕΠΑΝΩ|ΕΠΕΙΔΗ|ΕΠΕΙΤΑ|ΕΠΗ|ΕΠΙ|ΕΠΙΣΗΣ|ΕΠΟΜΕΝΩΣ|ΕΡΑ|ΕΣ|ΕΣΑΣ|ΕΣΕ|ΕΣΕΙΣ|ΕΣΕΝΑ|ΕΣΗ|ΕΣΤΩ|ΕΣΥ|ΕΣΩ|ΕΤΙ|ΕΤΣΙ|ΕΥ|ΕΥΑ|ΕΥΓΕ|ΕΥΘΥΣ|ΕΥΤΥΧΩΣ|ΕΦΕ|ΕΦΕΞΗΣ|ΕΦΤ|ΕΧΕ|ΕΧΕΙ|ΕΧΕΙΣ|ΕΧΕΤΕ|ΕΧΘΕΣ|ΕΧΟΜΕ|ΕΧΟΥΜΕ|ΕΧΟΥΝ|ΕΧΤΕΣ|ΕΧΩ|ΕΩΣ|ΖΕΑ|ΖΕΗ|ΖΕΙ|ΖΕΝ|ΖΗΝ|ΖΩ|Η|ΗΔΗ|ΗΔΥ|ΗΘΗ|ΗΛΟ|ΗΜΙ|ΗΠΑ|ΗΣΑΣΤΕ|ΗΣΟΥΝ|ΗΤΑ|ΗΤΑΝ|ΗΤΑΝΕ|ΗΤΟΙ|ΗΤΤΟΝ|ΗΩ|ΘΑ|ΘΥΕ|ΘΩΡ|Ι|ΙΑ|ΙΒΟ|ΙΔΗ|ΙΔΙΩΣ|ΙΕ|ΙΙ|ΙΙΙ|ΙΚΑ|ΙΛΟ|ΙΜΑ|ΙΝΑ|ΙΝΩ|ΙΞΕ|ΙΞΟ|ΙΟ|ΙΟΙ|ΙΣΑ|ΙΣΑΜΕ|ΙΣΕ|ΙΣΗ|ΙΣΙΑ|ΙΣΟ|ΙΣΩΣ|ΙΩΒ|ΙΩΝ|ΙΩΣ|ΙΑΝ|ΚΑΘ|ΚΑΘΕ|ΚΑΘΕΤΙ|ΚΑΘΟΛΟΥ|ΚΑΘΩΣ|ΚΑΙ|ΚΑΝ|ΚΑΠΟΤΕ|ΚΑΠΟΥ|ΚΑΠΩΣ|ΚΑΤ|ΚΑΤΑ|ΚΑΤΙ|ΚΑΤΙΤΙ|ΚΑΤΟΠΙΝ|ΚΑΤΩ|ΚΑΩ|ΚΒΟ|ΚΕΑ|ΚΕΙ|ΚΕΝ|ΚΙ|ΚΙΜ|ΚΙΟΛΑΣ|ΚΙΤ|ΚΙΧ|ΚΚΕ|ΚΛΙΣΕ|ΚΛΠ|ΚΟΚ|ΚΟΝΤΑ|ΚΟΧ|ΚΤΛ|ΚΥΡ|ΚΥΡΙΩΣ|ΚΩ|ΚΩΝ|ΛΑ|ΛΕΑ|ΛΕΝ|ΛΕΟ|ΛΙΑ|ΛΙΓΑΚΙ|ΛΙΓΟΥΛΑΚΙ|ΛΙΓΟ|ΛΙΓΩΤΕΡΟ|ΛΙΟ|ΛΙΡ|ΛΟΓΩ|ΛΟΙΠΑ|ΛΟΙΠΟΝ|ΛΟΣ|ΛΣ|ΛΥΩ|ΜΑ|ΜΑΖΙ|ΜΑΚΑΡΙ|ΜΑΛΙΣΤΑ|ΜΑΛΛΟΝ|ΜΑΝ|ΜΑΞ|ΜΑΣ|ΜΑΤ|ΜΕ|ΜΕΘΑΥΡΙΟ|ΜΕΙ|ΜΕΙΟΝ|ΜΕΛ|ΜΕΛΕΙ|ΜΕΛΛΕΤΑΙ|ΜΕΜΙΑΣ|ΜΕΝ|ΜΕΣ|ΜΕΣΑ|ΜΕΤ|ΜΕΤΑ|ΜΕΤΑΞΥ|ΜΕΧΡΙ|ΜΗ|ΜΗΔΕ|ΜΗΝ|ΜΗΠΩΣ|ΜΗΤΕ|ΜΙ|ΜΙΞ|ΜΙΣ|ΜΜΕ|ΜΝΑ|ΜΟΒ|ΜΟΛΙΣ|ΜΟΛΟΝΟΤΙ|ΜΟΝΑΧΑ|ΜΟΝΟΜΙΑΣ|ΜΙΑ|ΜΟΥ|ΜΠΑ|ΜΠΟΡΕΙ|ΜΠΟΡΟΥΝ|ΜΠΡΑΒΟ|ΜΠΡΟΣ|ΜΠΩ|ΜΥ|ΜΥΑ|ΜΥΝ|ΝΑ|ΝΑΕ|ΝΑΙ|ΝΑΟ|ΝΔ|ΝΕΐ|ΝΕΑ|ΝΕΕ|ΝΕΟ|ΝΙ|ΝΙΑ|ΝΙΚ|ΝΙΛ|ΝΙΝ|ΝΙΟ|ΝΤΑ|ΝΤΕ|ΝΤΙ|ΝΤΟ|ΝΥΝ|ΝΩΕ|ΝΩΡΙΣ|ΞΑΝΑ|ΞΑΦΝΙΚΑ|ΞΕΩ|ΞΙ|Ο|ΟΑ|ΟΑΠ|ΟΔΟ|ΟΕ|ΟΖΟ|ΟΗΕ|ΟΙ|ΟΙΑ|ΟΙΗ|ΟΚΑ|ΟΛΟΓΥΡΑ|ΟΛΟΝΕΝ|ΟΛΟΤΕΛΑ|ΟΛΩΣΔΙΟΛΟΥ|ΟΜΩΣ|ΟΝ|ΟΝΕ|ΟΝΟ|ΟΠΑ|ΟΠΕ|ΟΠΗ|ΟΠΟ|ΟΠΟΙΑΔΗΠΟΤΕ|ΟΠΟΙΑΝΔΗΠΟΤΕ|ΟΠΟΙΑΣΔΗΠΟΤΕ|ΟΠΟΙΔΗΠΟΤΕ|ΟΠΟΙΕΣΔΗΠΟΤΕ|ΟΠΟΙΟΔΗΠΟΤΕ|ΟΠΟΙΟΝΔΗΠΟΤΕ|ΟΠΟΙΟΣΔΗΠΟΤΕ|ΟΠΟΙΟΥΔΗΠΟΤΕ|ΟΠΟΙΟΥΣΔΗΠΟΤΕ|ΟΠΟΙΩΝΔΗΠΟΤΕ|ΟΠΟΤΕΔΗΠΟΤΕ|ΟΠΟΥ|ΟΠΟΥΔΗΠΟΤΕ|ΟΠΩΣ|ΟΡΑ|ΟΡΕ|ΟΡΗ|ΟΡΟ|ΟΡΦ|ΟΡΩ|ΟΣΑ|ΟΣΑΔΗΠΟΤΕ|ΟΣΕ|ΟΣΕΣΔΗΠΟΤΕ|ΟΣΗΔΗΠΟΤΕ|ΟΣΗΝΔΗΠΟΤΕ|ΟΣΗΣΔΗΠΟΤΕ|ΟΣΟΔΗΠΟΤΕ|ΟΣΟΙΔΗΠΟΤΕ|ΟΣΟΝΔΗΠΟΤΕ|ΟΣΟΣΔΗΠΟΤΕ|ΟΣΟΥΔΗΠΟΤΕ|ΟΣΟΥΣΔΗΠΟΤΕ|ΟΣΩΝΔΗΠΟΤΕ|ΟΤΑΝ|ΟΤΕ|ΟΤΙ|ΟΤΙΔΗΠΟΤΕ|ΟΥ|ΟΥΔΕ|ΟΥΚ|ΟΥΣ|ΟΥΤΕ|ΟΥΦ|ΟΧΙ|ΟΨΑ|ΟΨΕ|ΟΨΗ|ΟΨΙ|ΟΨΟ|ΠΑ|ΠΑΛΙ|ΠΑΝ|ΠΑΝΤΟΤΕ|ΠΑΝΤΟΥ|ΠΑΝΤΩΣ|ΠΑΠ|ΠΑΡ|ΠΑΡΑ|ΠΕΙ|ΠΕΡ|ΠΕΡΑ|ΠΕΡΙ|ΠΕΡΙΠΟΥ|ΠΕΡΣΙ|ΠΕΡΥΣΙ|ΠΕΣ|ΠΙ|ΠΙΑ|ΠΙΘΑΝΟΝ|ΠΙΚ|ΠΙΟ|ΠΙΣΩ|ΠΙΤ|ΠΙΩ|ΠΛΑΙ|ΠΛΕΟΝ|ΠΛΗΝ|ΠΛΩ|ΠΜ|ΠΟΑ|ΠΟΕ|ΠΟΛ|ΠΟΛΥ|ΠΟΠ|ΠΟΤΕ|ΠΟΥ|ΠΟΥΘΕ|ΠΟΥΘΕΝΑ|ΠΡΕΠΕΙ|ΠΡΙ|ΠΡΙΝ|ΠΡΟ|ΠΡΟΚΕΙΜΕΝΟΥ|ΠΡΟΚΕΙΤΑΙ|ΠΡΟΠΕΡΣΙ|ΠΡΟΣ|ΠΡΟΤΟΥ|ΠΡΟΧΘΕΣ|ΠΡΟΧΤΕΣ|ΠΡΩΤΥΤΕΡΑ|ΠΥΑ|ΠΥΞ|ΠΥΟ|ΠΥΡ|ΠΧ|ΠΩ|ΠΩΛ|ΠΩΣ|ΡΑ|ΡΑΙ|ΡΑΠ|ΡΑΣ|ΡΕ|ΡΕΑ|ΡΕΕ|ΡΕΙ|ΡΗΣ|ΡΘΩ|ΡΙΟ|ΡΟ|ΡΟΐ|ΡΟΕ|ΡΟΖ|ΡΟΗ|ΡΟΘ|ΡΟΙ|ΡΟΚ|ΡΟΛ|ΡΟΝ|ΡΟΣ|ΡΟΥ|ΣΑΙ|ΣΑΝ|ΣΑΟ|ΣΑΣ|ΣΕ|ΣΕΙΣ|ΣΕΚ|ΣΕΞ|ΣΕΡ|ΣΕΤ|ΣΕΦ|ΣΗΜΕΡΑ|ΣΙ|ΣΙΑ|ΣΙΓΑ|ΣΙΚ|ΣΙΧ|ΣΚΙ|ΣΟΙ|ΣΟΚ|ΣΟΛ|ΣΟΝ|ΣΟΣ|ΣΟΥ|ΣΡΙ|ΣΤΑ|ΣΤΗ|ΣΤΗΝ|ΣΤΗΣ|ΣΤΙΣ|ΣΤΟ|ΣΤΟΝ|ΣΤΟΥ|ΣΤΟΥΣ|ΣΤΩΝ|ΣΥ|ΣΥΓΧΡΟΝΩΣ|ΣΥΝ|ΣΥΝΑΜΑ|ΣΥΝΕΠΩΣ|ΣΥΝΗΘΩΣ|ΣΧΕΔΟΝ|ΣΩΣΤΑ|ΤΑ|ΤΑΔΕ|ΤΑΚ|ΤΑΝ|ΤΑΟ|ΤΑΥ|ΤΑΧΑ|ΤΑΧΑΤΕ|ΤΕ|ΤΕΙ|ΤΕΛ|ΤΕΛΙΚΑ|ΤΕΛΙΚΩΣ|ΤΕΣ|ΤΕΤ|ΤΖΟ|ΤΗ|ΤΗΛ|ΤΗΝ|ΤΗΣ|ΤΙ|ΤΙΚ|ΤΙΜ|ΤΙΠΟΤΑ|ΤΙΠΟΤΕ|ΤΙΣ|ΤΝΤ|ΤΟ|ΤΟΙ|ΤΟΚ|ΤΟΜ|ΤΟΝ|ΤΟΠ|ΤΟΣ|ΤΟΣ?Ν|ΤΟΣΑ|ΤΟΣΕΣ|ΤΟΣΗ|ΤΟΣΗΝ|ΤΟΣΗΣ|ΤΟΣΟ|ΤΟΣΟΙ|ΤΟΣΟΝ|ΤΟΣΟΣ|ΤΟΣΟΥ|ΤΟΣΟΥΣ|ΤΟΤΕ|ΤΟΥ|ΤΟΥΛΑΧΙΣΤΟ|ΤΟΥΛΑΧΙΣΤΟΝ|ΤΟΥΣ|ΤΣ|ΤΣΑ|ΤΣΕ|ΤΥΧΟΝ|ΤΩ|ΤΩΝ|ΤΩΡΑ|ΥΑΣ|ΥΒΑ|ΥΒΟ|ΥΙΕ|ΥΙΟ|ΥΛΑ|ΥΛΗ|ΥΝΙ|ΥΠ|ΥΠΕΡ|ΥΠΟ|ΥΠΟΨΗ|ΥΠΟΨΙΝ|ΥΣΤΕΡΑ|ΥΦΗ|ΥΨΗ|ΦΑ|ΦΑΐ|ΦΑΕ|ΦΑΝ|ΦΑΞ|ΦΑΣ|ΦΑΩ|ΦΕΖ|ΦΕΙ|ΦΕΤΟΣ|ΦΕΥ|ΦΙ|ΦΙΛ|ΦΙΣ|ΦΟΞ|ΦΠΑ|ΦΡΙ|ΧΑ|ΧΑΗ|ΧΑΛ|ΧΑΝ|ΧΑΦ|ΧΕ|ΧΕΙ|ΧΘΕΣ|ΧΙ|ΧΙΑ|ΧΙΛ|ΧΙΟ|ΧΛΜ|ΧΜ|ΧΟΗ|ΧΟΛ|ΧΡΩ|ΧΤΕΣ|ΧΩΡΙΣ|ΧΩΡΙΣΤΑ|ΨΕΣ|ΨΗΛΑ|ΨΙ|ΨΙΤ|Ω|ΩΑ|ΩΑΣ|ΩΔΕ|ΩΕΣ|ΩΘΩ|ΩΜΑ|ΩΜΕ|ΩΝ|ΩΟ|ΩΟΝ|ΩΟΥ|ΩΣ|ΩΣΑΝ|ΩΣΗ|ΩΣΟΤΟΥ|ΩΣΠΟΥ|ΩΣΤΕ|ΩΣΤΟΣΟ|ΩΤΑ|ΩΧ|ΩΩΝ)$/';

		if (preg_match($stop_words, $token))
		{
			return $this->returnStem($token, $w_CASE);
		}

		// step1list is used in Step 1. 41 stems
		$step1list             = Array();
		$step1list["ΦΑΓΙΑ"]    = "ΦΑ";
		$step1list["ΦΑΓΙΟΥ"]   = "ΦΑ";
		$step1list["ΦΑΓΙΩΝ"]   = "ΦΑ";
		$step1list["ΣΚΑΓΙΑ"]   = "ΣΚΑ";
		$step1list["ΣΚΑΓΙΟΥ"]  = "ΣΚΑ";
		$step1list["ΣΚΑΓΙΩΝ"]  = "ΣΚΑ";
		$step1list["ΟΛΟΓΙΟΥ"]  = "ΟΛΟ";
		$step1list["ΟΛΟΓΙΑ"]   = "ΟΛΟ";
		$step1list["ΟΛΟΓΙΩΝ"]  = "ΟΛΟ";
		$step1list["ΣΟΓΙΟΥ"]   = "ΣΟ";
		$step1list["ΣΟΓΙΑ"]    = "ΣΟ";
		$step1list["ΣΟΓΙΩΝ"]   = "ΣΟ";
		$step1list["ΤΑΤΟΓΙΑ"]  = "ΤΑΤΟ";
		$step1list["ΤΑΤΟΓΙΟΥ"] = "ΤΑΤΟ";
		$step1list["ΤΑΤΟΓΙΩΝ"] = "ΤΑΤΟ";
		$step1list["ΚΡΕΑΣ"]    = "ΚΡΕ";
		$step1list["ΚΡΕΑΤΟΣ"]  = "ΚΡΕ";
		$step1list["ΚΡΕΑΤΑ"]   = "ΚΡΕ";
		$step1list["ΚΡΕΑΤΩΝ"]  = "ΚΡΕ";
		$step1list["ΠΕΡΑΣ"]    = "ΠΕΡ";
		$step1list["ΠΕΡΑΤΟΣ"]  = "ΠΕΡ";
		// Added by Spyros. Also at $re in step1
		$step1list["ΠΕΡΑΤΗ"]     = "ΠΕΡ";
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

		// Vowels
		$v = '(Α|Ε|Η|Ι|Ο|Υ|Ω)';
		// Vowels without Y
		$v2 = '(Α|Ε|Η|Ι|Ο|Ω)';

		$test1 = true;

		// Step S1. 14 stems
		$re       = '/^(.+?)(ΙΖΑ|ΙΖΕΣ|ΙΖΕ|ΙΖΑΜΕ|ΙΖΑΤΕ|ΙΖΑΝ|ΙΖΑΝΕ|ΙΖΩ|ΙΖΕΙΣ|ΙΖΕΙ|ΙΖΟΥΜΕ|ΙΖΕΤΕ|ΙΖΟΥΝ|ΙΖΟΥΝΕ)$/';
		$exceptS1 = '/^(ΑΝΑΜΠΑ|ΕΜΠΑ|ΕΠΑ|ΞΑΝΑΠΑ|ΠΑ|ΠΕΡΙΠΑ|ΑΘΡΟ|ΣΥΝΑΘΡΟ|ΔΑΝΕ)$/';
		$exceptS2 = '/^(ΜΑΡΚ|ΚΟΡΝ|ΑΜΠΑΡ|ΑΡΡ|ΒΑΘΥΡΙ|ΒΑΡΚ|Β|ΒΟΛΒΟΡ|ΓΚΡ|ΓΛΥΚΟΡ|ΓΛΥΚΥΡ|ΙΜΠ|Λ|ΛΟΥ|ΜΑΡ|Μ|ΠΡ|ΜΠΡ|ΠΟΛΥΡ|Π|Ρ|ΠΙΠΕΡΟΡ)$/';

		if (preg_match($re, $token, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$token  = $stem . $step1list[$suffix];

			if (preg_match($exceptS1, $token))
			{
				$token = $token . 'I';
			}

			if (preg_match($exceptS2, $token))
			{
				$token = $token . 'IΖ';
			}

			return $this->returnStem($token, $w_CASE);
		}

		// Step S2. 7 stems
		$re       = '/^(.+?)(ΩΘΗΚΑ|ΩΘΗΚΕΣ|ΩΘΗΚΕ|ΩΘΗΚΑΜΕ|ΩΘΗΚΑΤΕ|ΩΘΗΚΑΝ|ΩΘΗΚΑΝΕ)$/';
		$exceptS1 = '/^(ΑΛ|ΒΙ|ΕΝ|ΥΨ|ΛΙ|ΖΩ|Σ|Χ)$/';

		if (preg_match($re, $token, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$token  = $stem . $step1list[$suffix];

			if (preg_match($exceptS1, $token))
			{
				$token = $token . 'ΩΝ';
			}

			return $this->returnStem($token, $w_CASE);
		}

		// Step S3. 7 stems
		$re       = '/^(.+?)(ΙΣΑ|ΙΣΕΣ|ΙΣΕ|ΙΣΑΜΕ|ΙΣΑΤΕ|ΙΣΑΝ|ΙΣΑΝΕ)$/';
		$exceptS1 = '/^(ΑΝΑΜΠΑ|ΑΘΡΟ|ΕΜΠΑ|ΕΣΕ|ΕΣΩΚΛΕ|ΕΠΑ|ΞΑΝΑΠΑ|ΕΠΕ|ΠΕΡΙΠΑ|ΑΘΡΟ|ΣΥΝΑΘΡΟ|ΔΑΝΕ|ΚΛΕ|ΧΑΡΤΟΠΑ|ΕΞΑΡΧΑ|ΜΕΤΕΠΕ|ΑΠΟΚΛΕ|ΑΠΕΚΛΕ|ΕΚΛΕ|ΠΕ|ΠΕΡΙΠΑ)$/';
		// $exceptS2 = '/^(ΑΝ|ΑΦ|ΓΕ|ΓΙΓΑΝΤΟΑΦ|ΓΚΕ|ΔΗΜΟΚΡΑΤ|ΚΟΜ|ΓΚ|Μ|Π|ΠΟΥΚΑΜ|ΟΛΟ|ΛΑΡ)$/';

		if ($token == "ΙΣΑ")
		{
			$token = "ΙΣ";

			return $token;
		}

		if (preg_match($re, $token, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$token  = $stem . $step1list[$suffix];

			if (preg_match($exceptS1, $token))
			{
				$token = $token . 'Ι';
			}

			return $this->returnStem($token, $w_CASE);
		}


		// Step S4. 7 stems
		$re       = '/^(.+?)(ΙΣΩ|ΙΣΕΙΣ|ΙΣΕΙ|ΙΣΟΥΜΕ|ΙΣΕΤΕ|ΙΣΟΥΝ|ΙΣΟΥΝΕ)$/';
		$exceptS1 = '/^(ΑΝΑΜΠΑ|ΕΜΠΑ|ΕΣΕ|ΕΣΩΚΛΕ|ΕΠΑ|ΞΑΝΑΠΑ|ΕΠΕ|ΠΕΡΙΠΑ|ΑΘΡΟ|ΣΥΝΑΘΡΟ|ΔΑΝΕ|ΚΛΕ|ΧΑΡΤΟΠΑ|ΕΞΑΡΧΑ|ΜΕΤΕΠΕ|ΑΠΟΚΛΕ|ΑΠΕΚΛΕ|ΕΚΛΕ|ΠΕ|ΠΕΡΙΠΑ)$/';

		if (preg_match($re, $token, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$token  = $stem . $step1list[$suffix];

			if (preg_match($exceptS1, $token))
			{
				$token = $token . 'Ι';
			}

			return $this->returnStem($token, $w_CASE);
		}

		// Step S5. 11 stems
		$re       = '/^(.+?)(ΙΣΤΟΣ|ΙΣΤΟΥ|ΙΣΤΟ|ΙΣΤΕ|ΙΣΤΟΙ|ΙΣΤΩΝ|ΙΣΤΟΥΣ|ΙΣΤΗ|ΙΣΤΗΣ|ΙΣΤΑ|ΙΣΤΕΣ)$/';
		$exceptS1 = '/^(Μ|Π|ΑΠ|ΑΡ|ΗΔ|ΚΤ|ΣΚ|ΣΧ|ΥΨ|ΦΑ|ΧΡ|ΧΤ|ΑΚΤ|ΑΟΡ|ΑΣΧ|ΑΤΑ|ΑΧΝ|ΑΧΤ|ΓΕΜ|ΓΥΡ|ΕΜΠ|ΕΥΠ|ΕΧΘ|ΗΦΑ|ΉΦΑ|ΚΑΘ|ΚΑΚ|ΚΥΛ|ΛΥΓ|ΜΑΚ|ΜΕΓ|ΤΑΧ|ΦΙΛ|ΧΩΡ)$/';
		$exceptS2 = '/^(ΔΑΝΕ|ΣΥΝΑΘΡΟ|ΚΛΕ|ΣΕ|ΕΣΩΚΛΕ|ΑΣΕ|ΠΛΕ)$/';

		if (preg_match($re, $token, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$token  = $stem . $step1list[$suffix];

			if (preg_match($exceptS1, $token))
			{
				$token = $token . 'ΙΣΤ';
			}

			if (preg_match($exceptS2, $token))
			{
				$token = $token . 'Ι';
			}

			return $this->returnStem($token, $w_CASE);
		}

		// Step S6. 6 stems
		$re       = '/^(.+?)(ΙΣΜΟ|ΙΣΜΟΙ|ΙΣΜΟΣ|ΙΣΜΟΥ|ΙΣΜΟΥΣ|ΙΣΜΩΝ)$/';
		$exceptS1 = '/^(ΑΓΝΩΣΤΙΚ|ΑΤΟΜΙΚ|ΓΝΩΣΤΙΚ|ΕΘΝΙΚ|ΕΚΛΕΚΤΙΚ|ΣΚΕΠΤΙΚ|ΤΟΠΙΚ)$/';
		$exceptS2 = '/^(ΣΕ|ΜΕΤΑΣΕ|ΜΙΚΡΟΣΕ|ΕΓΚΛΕ|ΑΠΟΚΛΕ)$/';
		$exceptS3 = '/^(ΔΑΝΕ|ΑΝΤΙΔΑΝΕ)$/';
		$exceptS4 = '/^(ΑΛΕΞΑΝΔΡΙΝ|ΒΥΖΑΝΤΙΝ|ΘΕΑΤΡΙΝ)$/';

		if (preg_match($re, $token, $match))
		{
			$stem   = $match[1];
			$token  = $stem;

			if (preg_match($exceptS1, $token))
			{
				$token = str_replace('ΙΚ', "", $token);
			}

			if (preg_match($exceptS2, $token))
			{
				$token = $token . "ΙΣΜ";
			}

			if (preg_match($exceptS3, $token))
			{
				$token = $token . "Ι";
			}

			if (preg_match($exceptS4, $token))
			{
				$token = str_replace('ΙΝ', "", $token);
			}

			return $this->returnStem($token, $w_CASE);
		}

		// Step S7. 4 stems
		$re       = '/^(.+?)(ΑΡΑΚΙ|ΑΡΑΚΙΑ|ΟΥΔΑΚΙ|ΟΥΔΑΚΙΑ)$/';
		$exceptS1 = '/^(Σ|Χ)$/';

		if (preg_match($re, $token, $match))
		{
			$stem   = $match[1];
			$token  = $stem;

			if (preg_match($exceptS1, $token))
			{
				$token = $token . "AΡΑΚ";
			}

			return $this->returnStem($token, $w_CASE);
		}


		// Step S8. 8 stems
		$re       = '/^(.+?)(ΑΚΙ|ΑΚΙΑ|ΙΤΣΑ|ΙΤΣΑΣ|ΙΤΣΕΣ|ΙΤΣΩΝ|ΑΡΑΚΙ|ΑΡΑΚΙΑ)$/';
		$exceptS1 = '/^(ΑΝΘΡ|ΒΑΜΒ|ΒΡ|ΚΑΙΜ|ΚΟΝ|ΚΟΡ|ΛΑΒΡ|ΛΟΥΛ|ΜΕΡ|ΜΟΥΣΤ|ΝΑΓΚΑΣ|ΠΛ|Ρ|ΡΥ|Σ|ΣΚ|ΣΟΚ|ΣΠΑΝ|ΤΖ|ΦΑΡΜ|Χ|ΚΑΠΑΚ|ΑΛΙΣΦ|ΑΜΒΡ|ΑΝΘΡ|Κ|ΦΥΛ|ΚΑΤΡΑΠ|ΚΛΙΜ|ΜΑΛ|ΣΛΟΒ|Φ|ΣΦ|ΤΣΕΧΟΣΛΟΒ)$/';
		$exceptS2 = '/^(Β|ΒΑΛ|ΓΙΑΝ|ΓΛ|Ζ|ΗΓΟΥΜΕΝ|ΚΑΡΔ|ΚΟΝ|ΜΑΚΡΥΝ|ΝΥΦ|ΠΑΤΕΡ|Π|ΣΚ|ΤΟΣ|ΤΡΙΠΟΛ)$/';
		$exceptS3 = '/(ΚΟΡ)$/';// for words like ΠΛΟΥΣΙΟΚΟΡΙΤΣΑ, ΠΑΛΙΟΚΟΡΙΤΣΑ etc

		if (preg_match($re, $token, $match))
		{
			$stem   = $match[1];
			$token  = $stem;

			if (preg_match($exceptS1, $token))
			{
				$token = $token . "ΑΚ";
			}

			if (preg_match($exceptS2, $token))
			{
				$token = $token . "ΙΤΣ";
			}

			if (preg_match($exceptS3, $token))
			{
				$token = $token . "ΙΤΣ";
			}

			return $this->returnStem($token, $w_CASE);
		}

		// Step S9. 3 stems
		$re       = '/^(.+?)(ΙΔΙΟ|ΙΔΙΑ|ΙΔΙΩΝ)$/';
		$exceptS1 = '/^(ΑΙΦΝ|ΙΡ|ΟΛΟ|ΨΑΛ)$/';
		$exceptS2 = '/(Ε|ΠΑΙΧΝ)$/';

		if (preg_match($re, $token, $match))
		{
			$stem   = $match[1];
			$token  = $stem;

			if (preg_match($exceptS1, $token))
			{
				$token = $token . "ΙΔ";
			}

			if (preg_match($exceptS2, $token))
			{
				$token = $token . "ΙΔ";
			}

			return $this->returnStem($token, $w_CASE);
		}

		// Step S10. 4 stems
		$re       = '/^(.+?)(ΙΣΚΟΣ|ΙΣΚΟΥ|ΙΣΚΟ|ΙΣΚΕ)$/';
		$exceptS1 = '/^(Δ|ΙΒ|ΜΗΝ|Ρ|ΦΡΑΓΚ|ΛΥΚ|ΟΒΕΛ)$/';

		if (preg_match($re, $token, $match))
		{
			$stem   = $match[1];
			$token  = $stem;

			if (preg_match($exceptS1, $token))
			{
				$token = $token . "ΙΣΚ";
			}

			return $this->returnStem($token, $w_CASE);
		}

		// Step 1
		$re = '/(.*)(ΦΑΓΙΑ|ΦΑΓΙΟΥ|ΦΑΓΙΩΝ|ΣΚΑΓΙΑ|ΣΚΑΓΙΟΥ|ΣΚΑΓΙΩΝ|ΟΛΟΓΙΟΥ|ΟΛΟΓΙΑ|ΟΛΟΓΙΩΝ|ΣΟΓΙΟΥ|ΣΟΓΙΑ|ΣΟΓΙΩΝ|ΤΑΤΟΓΙΑ|ΤΑΤΟΓΙΟΥ|ΤΑΤΟΓΙΩΝ|ΚΡΕΑΣ|ΚΡΕΑΤΟΣ|ΚΡΕΑΤΑ|ΚΡΕΑΤΩΝ|ΠΕΡΑΣ|ΠΕΡΑΤΟΣ|ΠΕΡΑΤΗ|ΠΕΡΑΤΑ|ΠΕΡΑΤΩΝ|ΤΕΡΑΣ|ΤΕΡΑΤΟΣ|ΤΕΡΑΤΑ|ΤΕΡΑΤΩΝ|ΦΩΣ|ΦΩΤΟΣ|ΦΩΤΑ|ΦΩΤΩΝ|ΚΑΘΕΣΤΩΣ|ΚΑΘΕΣΤΩΤΟΣ|ΚΑΘΕΣΤΩΤΑ|ΚΑΘΕΣΤΩΤΩΝ|ΓΕΓΟΝΟΣ|ΓΕΓΟΝΟΤΟΣ|ΓΕΓΟΝΟΤΑ|ΓΕΓΟΝΟΤΩΝ)$/';

		if (preg_match($re, $token, $match))
		{
			$stem   = $match[1];
			$suffix = $match[2];
			$token  = $stem . $step1list[$suffix];
			$test1  = false;
		}

		// Step 2a. 2 stems
		$re = '/^(.+?)(ΑΔΕΣ|ΑΔΩΝ)$/';

		if (preg_match($re, $token, $match))
		{
			$stem  = $match[1];
			$token = $stem;
			$re    = '/(ΟΚ|ΜΑΜ|ΜΑΝ|ΜΠΑΜΠ|ΠΑΤΕΡ|ΓΙΑΓΙ|ΝΤΑΝΤ|ΚΥΡ|ΘΕΙ|ΠΕΘΕΡ)$/';

			if (!preg_match($re, $token))
			{
				$token = $token . "ΑΔ";
			}
		}

		// Step 2b. 2 stems
		$re = '/^(.+?)(ΕΔΕΣ|ΕΔΩΝ)$/';

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem   = $match[1];
			$token  = $stem;
			$exept2 = '/(ΟΠ|ΙΠ|ΕΜΠ|ΥΠ|ΓΗΠ|ΔΑΠ|ΚΡΑΣΠ|ΜΙΛ)$/';

			if (preg_match($exept2, $token))
			{
				$token = $token . 'ΕΔ';
			}
		}

		// Step 2c
		$re = '/^(.+?)(ΟΥΔΕΣ|ΟΥΔΩΝ)$/';

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem  = $match[1];
			$token = $stem;

			$exept3 = '/(ΑΡΚ|ΚΑΛΙΑΚ|ΠΕΤΑΛ|ΛΙΧ|ΠΛΕΞ|ΣΚ|Σ|ΦΛ|ΦΡ|ΒΕΛ|ΛΟΥΛ|ΧΝ|ΣΠ|ΤΡΑΓ|ΦΕ)$/';

			if (preg_match($exept3, $token))
			{
				$token = $token . 'ΟΥΔ';
			}
		}

		// Step 2d
		$re = '/^(.+?)(ΕΩΣ|ΕΩΝ)$/';

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem   = $match[1];
			$token  = $stem;
			$test1  = false;
			$exept4 = '/^(Θ|Δ|ΕΛ|ΓΑΛ|Ν|Π|ΙΔ|ΠΑΡ)$/';

			if (preg_match($exept4, $token))
			{
				$token = $token . 'Ε';
			}

		}

		// Step 3
		$re = '/^(.+?)(ΙΑ|ΙΟΥ|ΙΩΝ)$/';

		if (preg_match($re, $token, $fp))
		{
			$stem  = $fp[1];
			$token = $stem;
			$re    = '/' . $v . '$/';
			$test1 = false;

			if (preg_match($re, $token))
			{
				$token = $stem . 'Ι';
			}
		}

		// Step 4
		$re = '/^(.+?)(ΙΚΑ|ΙΚΟ|ΙΚΟΥ|ΙΚΩΝ)$/';

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem   = $match[1];
			$token  = $stem;
			$test1  = false;
			$re     = '/' . $v . '$/';
			$exept5 = '/^(ΑΛ|ΑΔ|ΕΝΔ|ΑΜΑΝ|ΑΜΜΟΧΑΛ|ΗΘ|ΑΝΗΘ|ΑΝΤΙΔ|ΦΥΣ|ΒΡΩΜ|ΓΕΡ|ΕΞΩΔ|ΚΑΛΠ|ΚΑΛΛΙΝ|ΚΑΤΑΔ|ΜΟΥΛ|ΜΠΑΝ|ΜΠΑΓΙΑΤ|ΜΠΟΛ|ΜΠΟΣ|ΝΙΤ|ΞΙΚ|ΣΥΝΟΜΗΛ|ΠΕΤΣ|ΠΙΤΣ|ΠΙΚΑΝΤ|ΠΛΙΑΤΣ|ΠΟΣΤΕΛΝ|ΠΡΩΤΟΔ|ΣΕΡΤ|ΣΥΝΑΔ|ΤΣΑΜ|ΥΠΟΔ|ΦΙΛΟΝ|ΦΥΛΟΔ|ΧΑΣ)$/';

			if (preg_match($re, $token) || preg_match($exept5, $token))
			{
				$token = $token . 'ΙΚ';
			}
		}

		// Step 5a
		$re  = '/^(.+?)(ΑΜΕ)$/';
		$re2 = '/^(.+?)(ΑΓΑΜΕ|ΗΣΑΜΕ|ΟΥΣΑΜΕ|ΗΚΑΜΕ|ΗΘΗΚΑΜΕ)$/';

		if ($token == "ΑΓΑΜΕ")
		{
			$token = "ΑΓΑΜ";

		}

		if (preg_match($re2, $token))
		{
			preg_match($re2, $token, $match);
			$stem  = $match[1];
			$token = $stem;
			$test1 = false;
		}

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem   = $match[1];
			$token  = $stem;
			$test1  = false;
			$exept6 = '/^(ΑΝΑΠ|ΑΠΟΘ|ΑΠΟΚ|ΑΠΟΣΤ|ΒΟΥΒ|ΞΕΘ|ΟΥΛ|ΠΕΘ|ΠΙΚΡ|ΠΟΤ|ΣΙΧ|Χ)$/';

			if (preg_match($exept6, $token))
			{
				$token = $token . "ΑΜ";
			}
		}

		// Step 5b
		$re2 = '/^(.+?)(ΑΝΕ)$/';
		$re3 = '/^(.+?)(ΑΓΑΝΕ|ΗΣΑΝΕ|ΟΥΣΑΝΕ|ΙΟΝΤΑΝΕ|ΙΟΤΑΝΕ|ΙΟΥΝΤΑΝΕ|ΟΝΤΑΝΕ|ΟΤΑΝΕ|ΟΥΝΤΑΝΕ|ΗΚΑΝΕ|ΗΘΗΚΑΝΕ)$/';

		if (preg_match($re3, $token))
		{
			preg_match($re3, $token, $match);
			$stem  = $match[1];
			$token = $stem;
			$test1 = false;
			$re3   = '/^(ΤΡ|ΤΣ)$/';

			if (preg_match($re3, $token))
			{
				$token = $token . "ΑΓΑΝ";
			}
		}

		if (preg_match($re2, $token))
		{
			preg_match($re2, $token, $match);
			$stem   = $match[1];
			$token  = $stem;
			$test1  = false;
			$re2    = '/' . $v2 . '$/';
			$exept7 = '/^(ΒΕΤΕΡ|ΒΟΥΛΚ|ΒΡΑΧΜ|Γ|ΔΡΑΔΟΥΜ|Θ|ΚΑΛΠΟΥΖ|ΚΑΣΤΕΛ|ΚΟΡΜΟΡ|ΛΑΟΠΛ|ΜΩΑΜΕΘ|Μ|ΜΟΥΣΟΥΛΜ|Ν|ΟΥΛ|Π|ΠΕΛΕΚ|ΠΛ|ΠΟΛΙΣ|ΠΟΡΤΟΛ|ΣΑΡΑΚΑΤΣ|ΣΟΥΛΤ|ΤΣΑΡΛΑΤ|ΟΡΦ|ΤΣΙΓΓ|ΤΣΟΠ|ΦΩΤΟΣΤΕΦ|Χ|ΨΥΧΟΠΛ|ΑΓ|ΟΡΦ|ΓΑΛ|ΓΕΡ|ΔΕΚ|ΔΙΠΛ|ΑΜΕΡΙΚΑΝ|ΟΥΡ|ΠΙΘ|ΠΟΥΡΙΤ|Σ|ΖΩΝΤ|ΙΚ|ΚΑΣΤ|ΚΟΠ|ΛΙΧ|ΛΟΥΘΗΡ|ΜΑΙΝΤ|ΜΕΛ|ΣΙΓ|ΣΠ|ΣΤΕΓ|ΤΡΑΓ|ΤΣΑΓ|Φ|ΕΡ|ΑΔΑΠ|ΑΘΙΓΓ|ΑΜΗΧ|ΑΝΙΚ|ΑΝΟΡΓ|ΑΠΗΓ|ΑΠΙΘ|ΑΤΣΙΓΓ|ΒΑΣ|ΒΑΣΚ|ΒΑΘΥΓΑΛ|ΒΙΟΜΗΧ|ΒΡΑΧΥΚ|ΔΙΑΤ|ΔΙΑΦ|ΕΝΟΡΓ|ΘΥΣ|ΚΑΠΝΟΒΙΟΜΗΧ|ΚΑΤΑΓΑΛ|ΚΛΙΒ|ΚΟΙΛΑΡΦ|ΛΙΒ|ΜΕΓΛΟΒΙΟΜΗΧ|ΜΙΚΡΟΒΙΟΜΗΧ|ΝΤΑΒ|ΞΗΡΟΚΛΙΒ|ΟΛΙΓΟΔΑΜ|ΟΛΟΓΑΛ|ΠΕΝΤΑΡΦ|ΠΕΡΗΦ|ΠΕΡΙΤΡ|ΠΛΑΤ|ΠΟΛΥΔΑΠ|ΠΟΛΥΜΗΧ|ΣΤΕΦ|ΤΑΒ|ΤΕΤ|ΥΠΕΡΗΦ|ΥΠΟΚΟΠ|ΧΑΜΗΛΟΔΑΠ|ΨΗΛΟΤΑΒ)$/';


			if (preg_match($re2, $token) || preg_match($exept7, $token))
			{
				$token = $token . "ΑΝ";
			}
		}

		// Step 5c
		$re3 = '/^(.+?)(ΕΤΕ)$/';
		$re4 = '/^(.+?)(ΗΣΕΤΕ)$/';

		if (preg_match($re4, $token))
		{
			preg_match($re4, $token, $match);
			$stem  = $match[1];
			$token = $stem;
			$test1 = false;
		}

		if (preg_match($re3, $token))
		{
			preg_match($re3, $token, $match);
			$stem   = $match[1];
			$token  = $stem;
			$test1  = false;
			$re3    = '/' . $v2 . '$/';
			$exept8 = '/(ΟΔ|ΑΙΡ|ΦΟΡ|ΤΑΘ|ΔΙΑΘ|ΣΧ|ΕΝΔ|ΕΥΡ|ΤΙΘ|ΥΠΕΡΘ|ΡΑΘ|ΕΝΘ|ΡΟΘ|ΣΘ|ΠΥΡ|ΑΙΝ|ΣΥΝΔ|ΣΥΝ|ΣΥΝΘ|ΧΩΡ|ΠΟΝ|ΒΡ|ΚΑΘ|ΕΥΘ|ΕΚΘ|ΝΕΤ|ΡΟΝ|ΑΡΚ|ΒΑΡ|ΒΟΛ|ΩΦΕΛ)$/';
			$exept9 = '/^(ΑΒΑΡ|ΒΕΝ|ΕΝΑΡ|ΑΒΡ|ΑΔ|ΑΘ|ΑΝ|ΑΠΛ|ΒΑΡΟΝ|ΝΤΡ|ΣΚ|ΚΟΠ|ΜΠΟΡ|ΝΙΦ|ΠΑΓ|ΠΑΡΑΚΑΛ|ΣΕΡΠ|ΣΚΕΛ|ΣΥΡΦ|ΤΟΚ|Υ|Δ|ΕΜ|ΘΑΡΡ|Θ)$/';

			if (preg_match($re3, $token) || preg_match($exept8, $token) || preg_match($exept9, $token))
			{
				$token = $token . "ΕΤ";
			}
		}

		// Step 5d
		$re = '/^(.+?)(ΟΝΤΑΣ|ΩΝΤΑΣ)$/';

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem    = $match[1];
			$token   = $stem;
			$test1   = false;
			$exept10 = '/^(ΑΡΧ)$/';
			$exept11 = '/(ΚΡΕ)$/';

			if (preg_match($exept10, $token))
			{
				$token = $token . "ΟΝΤ";
			}

			if (preg_match($exept11, $token))
			{
				$token = $token . "ΩΝΤ";
			}
		}

		// Step 5e
		$re = '/^(.+?)(ΟΜΑΣΤΕ|ΙΟΜΑΣΤΕ)$/';

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem    = $match[1];
			$token   = $stem;
			$test1   = false;
			$exept11 = '/^(ΟΝ)$/';

			if (preg_match($exept11, $token))
			{
				$token = $token . "ΟΜΑΣΤ";
			}
		}

		// Step 5f
		$re  = '/^(.+?)(ΕΣΤΕ)$/';
		$re2 = '/^(.+?)(ΙΕΣΤΕ)$/';

		if (preg_match($re2, $token))
		{
			preg_match($re2, $token, $match);
			$stem  = $match[1];
			$token = $stem;
			$test1 = false;
			$re2   = '/^(Π|ΑΠ|ΣΥΜΠ|ΑΣΥΜΠ|ΑΚΑΤΑΠ|ΑΜΕΤΑΜΦ)$/';

			if (preg_match($re2, $token))
			{
				$token = $token . "ΙΕΣΤ";
			}
		}

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem    = $match[1];
			$token   = $stem;
			$test1   = false;
			$exept12 = '/^(ΑΛ|ΑΡ|ΕΚΤΕΛ|Ζ|Μ|Ξ|ΠΑΡΑΚΑΛ|ΑΡ|ΠΡΟ|ΝΙΣ)$/';

			if (preg_match($exept12, $token))
			{
				$token = $token . "ΕΣΤ";
			}
		}

		// Step 5g
		$re  = '/^(.+?)(ΗΚΑ|ΗΚΕΣ|ΗΚΕ)$/';
		$re2 = '/^(.+?)(ΗΘΗΚΑ|ΗΘΗΚΕΣ|ΗΘΗΚΕ)$/';

		if (preg_match($re2, $token))
		{
			preg_match($re2, $token, $match);
			$stem  = $match[1];
			$token = $stem;
			$test1 = false;
		}

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem    = $match[1];
			$token   = $stem;
			$test1   = false;
			$exept13 = '/(ΣΚΩΛ|ΣΚΟΥΛ|ΝΑΡΘ|ΣΦ|ΟΘ|ΠΙΘ)$/';
			$exept14 = '/^(ΔΙΑΘ|Θ|ΠΑΡΑΚΑΤΑΘ|ΠΡΟΣΘ|ΣΥΝΘ|)$/';

			if (preg_match($exept13, $token) || preg_match($exept14, $token))
			{
				$token = $token . "ΗΚ";
			}
		}


		// Step 5h
		$re = '/^(.+?)(ΟΥΣΑ|ΟΥΣΕΣ|ΟΥΣΕ)$/';

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem    = $match[1];
			$token   = $stem;
			$test1   = false;
			$exept15 = '/^(ΦΑΡΜΑΚ|ΧΑΔ|ΑΓΚ|ΑΝΑΡΡ|ΒΡΟΜ|ΕΚΛΙΠ|ΛΑΜΠΙΔ|ΛΕΧ|Μ|ΠΑΤ|Ρ|Λ|ΜΕΔ|ΜΕΣΑΖ|ΥΠΟΤΕΙΝ|ΑΜ|ΑΙΘ|ΑΝΗΚ|ΔΕΣΠΟΖ|ΕΝΔΙΑΦΕΡ|ΔΕ|ΔΕΥΤΕΡΕΥ|ΚΑΘΑΡΕΥ|ΠΛΕ|ΤΣΑ)$/';
			$exept16 = '/(ΠΟΔΑΡ|ΒΛΕΠ|ΠΑΝΤΑΧ|ΦΡΥΔ|ΜΑΝΤΙΛ|ΜΑΛΛ|ΚΥΜΑΤ|ΛΑΧ|ΛΗΓ|ΦΑΓ|ΟΜ|ΠΡΩΤ)$/';

			if (preg_match($exept15, $token) || preg_match($exept16, $token))
			{
				$token = $token . "ΟΥΣ";
			}
		}

		//Step 5i
		$re = '/^(.+?)(ΑΓΑ|ΑΓΕΣ|ΑΓΕ)$/';

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem    = $match[1];
			$token   = $stem;
			$test1   = false;
			$exept17 = '/^(ΨΟΦ|ΝΑΥΛΟΧ)$/';
			$exept20 = '/(ΚΟΛΛ)$/';
			$exept18 = '/^(ΑΒΑΣΤ|ΠΟΛΥΦ|ΑΔΗΦ|ΠΑΜΦ|Ρ|ΑΣΠ|ΑΦ|ΑΜΑΛ|ΑΜΑΛΛΙ|ΑΝΥΣΤ|ΑΠΕΡ|ΑΣΠΑΡ|ΑΧΑΡ|ΔΕΡΒΕΝ|ΔΡΟΣΟΠ|ΞΕΦ|ΝΕΟΠ|ΝΟΜΟΤ|ΟΛΟΠ|ΟΜΟΤ|ΠΡΟΣΤ|ΠΡΟΣΩΠΟΠ|ΣΥΜΠ|ΣΥΝΤ|Τ|ΥΠΟΤ|ΧΑΡ|ΑΕΙΠ|ΑΙΜΟΣΤ|ΑΝΥΠ|ΑΠΟΤ|ΑΡΤΙΠ|ΔΙΑΤ|ΕΝ|ΕΠΙΤ|ΚΡΟΚΑΛΟΠ|ΣΙΔΗΡΟΠ|Λ|ΝΑΥ|ΟΥΛΑΜ|ΟΥΡ|Π|ΤΡ|Μ)$/';
			$exept19 = '/(ΟΦ|ΠΕΛ|ΧΟΡΤ|ΛΛ|ΣΦ|ΡΠ|ΦΡ|ΠΡ|ΛΟΧ|ΣΜΗΝ)$/';

			if ((preg_match($exept18, $token) || preg_match($exept19, $token))
				&& !(preg_match($exept17, $token) || preg_match($exept20, $token)))
			{
				$token = $token . "ΑΓ";
			}
		}


		// Step 5j
		$re = '/^(.+?)(ΗΣΕ|ΗΣΟΥ|ΗΣΑ)$/';

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem    = $match[1];
			$token   = $stem;
			$test1   = false;
			$exept21 = '/^(Ν|ΧΕΡΣΟΝ|ΔΩΔΕΚΑΝ|ΕΡΗΜΟΝ|ΜΕΓΑΛΟΝ|ΕΠΤΑΝ)$/';

			if (preg_match($exept21, $token))
			{
				$token = $token . "ΗΣ";
			}
		}

		// Step 5k
		$re = '/^(.+?)(ΗΣΤΕ)$/';

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem    = $match[1];
			$token   = $stem;
			$test1   = false;
			$exept22 = '/^(ΑΣΒ|ΣΒ|ΑΧΡ|ΧΡ|ΑΠΛ|ΑΕΙΜΝ|ΔΥΣΧΡ|ΕΥΧΡ|ΚΟΙΝΟΧΡ|ΠΑΛΙΜΨ)$/';

			if (preg_match($exept22, $token))
			{
				$token = $token . "ΗΣΤ";
			}
		}

		//Step 5l
		$re = '/^(.+?)(ΟΥΝΕ|ΗΣΟΥΝΕ|ΗΘΟΥΝΕ)$/';

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem    = $match[1];
			$token   = $stem;
			$test1   = false;
			$exept23 = '/^(Ν|Ρ|ΣΠΙ|ΣΤΡΑΒΟΜΟΥΤΣ|ΚΑΚΟΜΟΥΤΣ|ΕΞΩΝ)$/';

			if (preg_match($exept23, $token))
			{
				$token = $token . "ΟΥΝ";
			}
		}

		//Step 5l
		$re = '/^(.+?)(ΟΥΜΕ|ΗΣΟΥΜΕ|ΗΘΟΥΜΕ)$/';

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem    = $match[1];
			$token   = $stem;
			$test1   = false;
			$exept24 = '/^(ΠΑΡΑΣΟΥΣ|Φ|Χ|ΩΡΙΟΠΛ|ΑΖ|ΑΛΛΟΣΟΥΣ|ΑΣΟΥΣ)$/';

			if (preg_match($exept24, $token))
			{
				$token = $token . "ΟΥΜ";
			}
		}

		// Step 6
		$re  = '/^(.+?)(ΜΑΤΑ|ΜΑΤΩΝ|ΜΑΤΟΣ)$/';
		$re2 = '/^(.+?)(Α|ΑΓΑΤΕ|ΑΓΑΝ|ΑΕΙ|ΑΜΑΙ|ΑΝ|ΑΣ|ΑΣΑΙ|ΑΤΑΙ|ΑΩ|Ε|ΕΙ|ΕΙΣ|ΕΙΤΕ|ΕΣΑΙ|ΕΣ|ΕΤΑΙ|Ι|ΙΕΜΑΙ|ΙΕΜΑΣΤΕ|ΙΕΤΑΙ|ΙΕΣΑΙ|ΙΕΣΑΣΤΕ|ΙΟΜΑΣΤΑΝ|ΙΟΜΟΥΝ|ΙΟΜΟΥΝΑ|ΙΟΝΤΑΝ|ΙΟΝΤΟΥΣΑΝ|ΙΟΣΑΣΤΑΝ|ΙΟΣΑΣΤΕ|ΙΟΣΟΥΝ|ΙΟΣΟΥΝΑ|ΙΟΤΑΝ|ΙΟΥΜΑ|ΙΟΥΜΑΣΤΕ|ΙΟΥΝΤΑΙ|ΙΟΥΝΤΑΝ|Η|ΗΔΕΣ|ΗΔΩΝ|ΗΘΕΙ|ΗΘΕΙΣ|ΗΘΕΙΤΕ|ΗΘΗΚΑΤΕ|ΗΘΗΚΑΝ|ΗΘΟΥΝ|ΗΘΩ|ΗΚΑΤΕ|ΗΚΑΝ|ΗΣ|ΗΣΑΝ|ΗΣΑΤΕ|ΗΣΕΙ|ΗΣΕΣ|ΗΣΟΥΝ|ΗΣΩ|Ο|ΟΙ|ΟΜΑΙ|ΟΜΑΣΤΑΝ|ΟΜΟΥΝ|ΟΜΟΥΝΑ|ΟΝΤΑΙ|ΟΝΤΑΝ|ΟΝΤΟΥΣΑΝ|ΟΣ|ΟΣΑΣΤΑΝ|ΟΣΑΣΤΕ|ΟΣΟΥΝ|ΟΣΟΥΝΑ|ΟΤΑΝ|ΟΥ|ΟΥΜΑΙ|ΟΥΜΑΣΤΕ|ΟΥΝ|ΟΥΝΤΑΙ|ΟΥΝΤΑΝ|ΟΥΣ|ΟΥΣΑΝ|ΟΥΣΑΤΕ|Υ|ΥΣ|Ω|ΩΝ)$/';

		if (preg_match($re, $token, $match))
		{
			$stem  = $match[1];
			$token = $stem . "ΜΑ";
		}

		if (preg_match($re2, $token) && $test1)
		{
			preg_match($re2, $token, $match);
			$stem  = $match[1];
			$token = $stem;
		}

		// Step 7 (ΠΑΡΑΘΕΤΙΚΑ)
		$re = '/^(.+?)(ΕΣΤΕΡ|ΕΣΤΑΤ|ΟΤΕΡ|ΟΤΑΤ|ΥΤΕΡ|ΥΤΑΤ|ΩΤΕΡ|ΩΤΑΤ)$/';

		if (preg_match($re, $token))
		{
			preg_match($re, $token, $match);
			$stem  = $match[1];
			$token = $stem;
		}

		return $this->returnStem($token, $w_CASE);
	}

	protected function returnStem($w, $w_CASE)
	{
		// Convert case back to initial by reading $w_CASE
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
			switch (@$w_CASE[$i])
			{
				default:
					// Not converted, no action
					break;

				case 1:
					// The latter was converted to uppercase, now it is converted back to lowercase
					for ($k = 0; $k <= 32; $k = $k + 1)
					{
						if ($w[$i] == $acceptedLetters[$k])
						{
							$w[$i] = $unacceptedLetters[$k];
						}
					}
					break;

				case 2:
					// Special case: final sigma was converted to uppercase sigma, converting back to final sigma
					$w[$i] = "ς";
					break;
			}
		}

		return $w;
	}
}