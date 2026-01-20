INSERT INTO `<DB_PREFIX>modules` (`id`, `name`, `name_const`, `description_const`, `icon_file`, `module_tables`, `dependent_modules`, `settings_page`, `settings_const`, `settings_access_by`, `management_page`, `management_const`, `management_access_by`, `is_installed`, `module_type`, `show_on_dashboard`, `priority_order`) VALUES
(NULL, 'conferences', '_CONFERENCES', '_MD_CONFERENCES', 'conferences.png', 'conference_types,conferences,conference_participants,conference_tuitions,specialities_management', '', 'mod_conferences_settings', '_CONFERENCE_SETTINGS', 'owner,mainadmin', 'mod_conference_types,mod_conferences_management,mod_conference_participants,mod_conference_tuitions,mod_specialities_management', '_CONFERENCE_TYPES,_CONFERENCES_MANAGEMENT,_CONFERENCE_PARTICIPANTS,_CONFERENCE_TUITIONS,_SPECIALITIES_MANAGEMENT', 'owner,mainadmin', 1, 2, 0, 3);


INSERT INTO `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name_const`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES
(NULL, 'conferences', 'is_active', 'yes', '_MSN_CONFERENCES_IS_ACTIVE', '_MS_CONFERENCES_IS_ACTIVE', 'yes/no', 1, '');


DROP TABLE IF EXISTS `<DB_PREFIX>conference_types`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>conference_types` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `priority_order` smallint(6) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2;

INSERT INTO `<DB_PREFIX>conference_types` (`id`, `priority_order`, `is_active`) VALUES (1, 0, 1);


DROP TABLE IF EXISTS `<DB_PREFIX>conference_types_translations`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>conference_types_translations` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `conference_type_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `language_id` varchar(2) CHARACTER SET latin1 NOT NULL DEFAULT 'en',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `short_description` varchar(2048) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `full_description` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2;

INSERT INTO `<DB_PREFIX>conference_types_translations` (`id`, `conference_type_id`, `language_id`, `name`, `short_description`, `full_description`)
(SELECT NULL, 1, abbreviation, 'Advanced Cardiac Life Support', '<p><span style="color: #404040; font-family: Arial, Helvetica, sans-serif; font-size: 13px;">Due to the inherent knowledge, skills, practice locations, and populations of patients served by practitioners of anesthesiology, emergency and critical care medicine, the physicians, nurses, and other advanced care providers are summoned to interpret the EKG reading and often treat the underlying dysrhythmias. This program is designed to update these practitioners with the latest information on this body of scientific information. This course offers 8 contact hours.</span></p>', '<p><span style="color: #404040; font-family: Arial, Helvetica, sans-serif; font-size: 13px;">Due to the inherent knowledge, skills, practice locations, and populations of patients served by practitioners of anesthesiology, emergency and critical care medicine, the physicians, nurses, and other advanced care providers are summoned to interpret the EKG reading and often treat the underlying dysrhythmias. This program is designed to update these practitioners with the latest information on this body of scientific information. This course offers 8 contact hours.</span></p>' FROM `<DB_PREFIX>languages`);


DROP TABLE IF EXISTS `<DB_PREFIX>conferences`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>conferences` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `conference_type_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `start_date` date NULL DEFAULT NULL,
  `finish_date` date NULL DEFAULT NULL,
  `hotel_location_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `hotel_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7;

INSERT INTO `<DB_PREFIX>conferences` (`id`, `conference_type_id`, `start_date`, `finish_date`, `hotel_location_id`, `hotel_id`, `is_active`) VALUES
(1, 1, NOW() - INTERVAL 2 DAY, NOW() + INTERVAL 50 DAY, 1, 2, 1),
(2, 1, NOW() - INTERVAL 12 DAY, NOW() + INTERVAL 45 DAY, 2, 1, 1),
(3, 1, NOW() - INTERVAL 20 DAY, NOW() + INTERVAL 45 DAY, 3, 3, 1),
(4, 1, NOW() - INTERVAL 30 DAY, NOW() + INTERVAL 30 DAY, 1, 2, 1),
(5, 1, NOW() - INTERVAL 45 DAY, NOW() + INTERVAL 25 DAY, 3, 3, 1),
(6, 1, NOW() - INTERVAL 45 DAY, NOW() + INTERVAL 20 DAY, 2, 1, 1);


DROP TABLE IF EXISTS `<DB_PREFIX>conferences_translations`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>conferences_translations` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `conference_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `language_id` varchar(2) CHARACTER SET latin1 NOT NULL DEFAULT 'en',
  `location_description` text(4096) COLLATE utf8_unicode_ci NOT NULL,
  `hotel_description` text(4096) COLLATE utf8_unicode_ci NOT NULL,
  `target_audience` text(4096) COLLATE utf8_unicode_ci NOT NULL,
  `program_purpose` text(4096) COLLATE utf8_unicode_ci NOT NULL,
  `objectives` text(4096) COLLATE utf8_unicode_ci NOT NULL,
  `program_schedule` text(8192) COLLATE utf8_unicode_ci NOT NULL,
  `faculty` text(4096) COLLATE utf8_unicode_ci NOT NULL,
  `accreditation` text(4096) COLLATE utf8_unicode_ci NOT NULL,
  `fap` text(4096) COLLATE utf8_unicode_ci NOT NULL,
  `course_cancellation_fee` text(4096) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7;

INSERT INTO `<DB_PREFIX>conferences_translations` (`id`, `conference_id`, `language_id`, `location_description`, `hotel_description`, `target_audience`, `program_purpose`, `objectives`, `program_schedule`, `faculty`, `accreditation`, `fap`, `course_cancellation_fee`)
(SELECT NULL, 1, abbreviation,
	'<p>Branson entertainment is plentiful and diverse, offering something for everyone. Voted the number one theme park in the world by the International Association of Amusement Parks &amp; Attractions, Silver Dollar City is a trip back in time to the day when the people living in these mountains created everything needed from local materials using their own hands.</p> <p>Replete with thrill rides and artisans, this is the combination of entertainment and education you’ve always wanted for a family fun-filled time. Or the scenery of the southwest Missouri or northwest Arkansas wilderness on the Branson Scenic Railway - “Mainline Through The Ozarks”. Branson also has the world’s largest Titanic museum. This and much more is here for your enjoyment.</p>',
	'<p class="bodyTopRight">Perfectly centered in downtown Branson, this AAA Four Diamond hotel prioritizes the comfort and satisfaction of all guests. Whether you are ready to explore Historic Downtown Branson, take your family to the local amusement park Silver Dollar City, or stroll along Lake Taneycomo, the Hilton Branson Convention Center offers guests a refreshing place to stay. Grab a bite to eat at a nearby casual restaurant, or dine at the hotel''s elegant Level 2 Steakhouse. Shoppers will love the Branson Landing area, as it is home to many specialty shops. Your store options are sure to appeal to one of your interests. Guests with varied tastes can indulge during their trip to Branson, MO, and have fun at this outdoor pavilion. Deluxe guest rooms feature a flat screen TV, mini-fridge, clock radio w/ MP3 connection, coffee maker, mini-fridge, hair dryer, iron/board, and more. The Hilton Branson Convention Center Hotel is located approximately 15 miles from the Branson Airport (BKG) and 40 miles from the Springfield Regional Airport (SGF).</p>',
	'<p>This course is designed for physicians, certified registered nurse anesthetists, physician assistants, nurse practitioners, registered nurses and other medical care providers who must maintain state-of-the-art knowledge of the specialty or are impacted by it.</p>',
	'<p>Presentations are designed to facilitate the physician, nurse specialist, and other providers maintaining skills of the same kind to review and update knowledge and abilities in one or more of these areas vital to the practicing professional:</p> <p><strong>Possess</strong> the scientific background for clinical practice</p> <p><strong>Maintain</strong> state-of-the-art knowledge of not only the specialty, but also of all additional related disciplines which may impact it</p> <p><strong>Maintain</strong> an up-to-the-minute armamentarium of knowledge and skills for the selection and use of complex equipment, pharmacological agents, and procedures necessary for the provision of quality patient care</p> <p><strong>Manage</strong> self and colleagues to function toward common goals in the clinical setting, the clinical and educational institutions, and the community in which the practice resides</p> <p><strong>Serve</strong> as an expert in matters involving health care delivery; serve as an informed manager of clinical and educational services provided, including the acquisition and distribution of resources necessary in meeting professional goals</p> <p><strong>Provide</strong> assistance with and support of other service providers, departments, institutions, and organizations dependent upon the professional expertise of the practitioner</p>',
	'<p>At the conclusion of this activity, the participant will be able to:</p> <p><strong>Apply</strong> the pertinent physical and behavioral sciences - to include but not be limited to advanced anatomy and physiology, pathophysiology, chemistry, physics, psychology, and social sciences - as they impact and are affected by the planning, delivery, and monitoring of anesthesia and related services inherent in the anesthesia professions.</p> <p><strong>Explain</strong> the selection, dosing considerations with methods of administration, safe use, and contraindications and precautions of presented pharmacological agents through the understanding of their physico-chemical properties, pharmacokinetics and pharmacodynamics, updated uses, and more recently developed additional, related, and similar drugs.</p> <p><strong>Outline</strong> comprehensive management plans for the group of patients discussed, whether related in age, physical status, cultural background, invasive procedure, anesthetic or analgesic requirements, clinical setting, adverse reactions, and/or goals of care.</p> <p><strong>Apply</strong> the principles of safety and asepsis in the performance of anesthetic procedures and administrations, equipment use, and other applications encountered during the provision of anesthesia and analgesia whether in the surgical unit, the labor and delivery suite, the pain management clinic, or other more remote location of anesthetizing services.</p> <p><strong>Incorporate </strong>the appropriate recommendations and/or requirements of pertinent external organizations, institutions, and professional groups - recognized as serving as the authority for and/or holding the responsibility to compile and approve such tenets - in the provision of anesthesia and related services, whether medical, legal, philosophical, ethical, or health care management standards.</p> <p><strong>Apply</strong> the new techniques in the clinical setting of the participant.</p>',
	'<table id="hotelTable" class="stripeMe highlightRes" border="0" cellspacing="0" cellpadding="0" width="100%"> <tbody> <tr valign="top"> <td colspan="5"> <h4>Thursday, May 11</h4> </td> </tr> <tr class="alt" valign="top"> <td width="1%"></td> <td width="6%">0700</td> <td colspan="3">Registration - Mandatory Sign In - Continental Breakfast</td> </tr> <tr valign="top"> <td></td> <td>0725</td> <td width="76%">Welcome</td> <td style="text-align: RIGHT;" width="15%">NWAS Staff</td> <td style="text-align: RIGHT;"></td> </tr> <tr class="alt" valign="top"> <td></td> <td>0730</td> <td>Preoperative Evaluation of the Ambulatory Patient</td> <td style="text-align: RIGHT;">J.Kryc</td> <td style="text-align: RIGHT;"></td> </tr> <tr valign="top"> <td></td> <td>0830</td> <td>Cleared for Surgery</td> <td style="text-align: RIGHT;">J.Kryc</td> <td style="text-align: RIGHT;"></td> </tr> <tr class="alt" valign="top"> <td></td> <td>0930</td> <td colspan="3">Break</td> </tr> <tr valign="top"> <td></td> <td>0945</td> <td>Office-Based Anesthesia: The Wild, Wild West of Healthcare</td> <td style="text-align: RIGHT;">J.Kryc</td> <td style="text-align: RIGHT;"></td> </tr> <tr class="alt" valign="top"> <td></td> <td>1045</td> <td>Herbal Stuff: It''s All Safe, Right?</td> <td style="text-align: RIGHT;">E.Frost</td> <td style="text-align: RIGHT;"></td> </tr> <tr valign="top"> <td></td> <td>1145</td> <td>How Do We Know Our Drugs Are Safe for Our Patients?</td> <td style="text-align: RIGHT;">E.Frost</td> <td style="text-align: RIGHT;"></td> </tr> <tr class="alt" valign="top"> <td></td> <td>1245</td> <td>Adjourn</td> <td style="text-align: RIGHT;">2.7 Pharmacology</td> <td style="text-align: RIGHT;"></td> </tr> <tr valign="top"> <td colspan="5"></td> </tr> <tr class="alt" valign="top"> <td colspan="5"> <h4>Friday, May 12</h4> </td> </tr> <tr valign="top"> <td></td> <td>0700</td> <td colspan="3">Registration - Mandatory Sign In - Continental Breakfast</td> </tr> <tr class="alt" valign="top"> <td></td> <td>0730</td> <td>Neuroanesthesia: Not Just for the Neuroanesthesiologist!</td> <td style="text-align: RIGHT;">E.Frost</td> <td style="text-align: RIGHT;"></td> </tr> <tr valign="top"> <td></td> <td>0830</td> <td>Spinal Cord Injury and Its Surgery: From Stem to Stern</td> <td style="text-align: RIGHT;">E.Frost</td> <td style="text-align: RIGHT;"></td> </tr> <tr class="alt" valign="top"> <td></td> <td>0930</td> <td colspan="3">Break</td> </tr> <tr valign="top"> <td></td> <td>0945</td> <td>PBLD: What Would You Do If: Crisis in Neuroanesthesia</td> <td style="text-align: RIGHT;">E.Frost</td> <td style="text-align: RIGHT;"></td> </tr> <tr class="alt" valign="top"> <td></td> <td>1045</td> <td>Statins and Noncardiac Surgery: Can We Reduce Perioperative Risk</td> <td style="text-align: RIGHT;">J.Kryc</td> <td style="text-align: RIGHT;"></td> </tr> <tr valign="top"> <td></td> <td>1145</td> <td>Malignant Hyperthermia: Some Like It Hot!</td> <td style="text-align: RIGHT;">J.Kryc</td> <td style="text-align: RIGHT;"></td> </tr> <tr class="alt" valign="top"> <td></td> <td>1245</td> <td>Adjourn</td> <td style="text-align: RIGHT;">1.5 Pharmacology</td> <td style="text-align: RIGHT;"></td> </tr> <tr valign="top"> <td></td> <td>1330</td> <td colspan="3"><a href="https://www.nwas.com/acls/17acls.html"><strong>Optional PALS*</strong></a></td> </tr> <tr class="alt" valign="top"> <td colspan="5"></td> </tr> <tr valign="top"> <td colspan="5"> <h4>Saturday, May 13</h4> </td> </tr> <tr class="alt" valign="top"> <td></td> <td>0700</td> <td colspan="3">Registration - Mandatory Sign In - Continental Breakfast</td> </tr> <tr valign="top"> <td></td> <td>0730</td> <td>Anesthetic Management of the Patient With Hypertension</td> <td style="text-align: RIGHT;">K.Domino</td> <td style="text-align: RIGHT;"></td> </tr> <tr class="alt" valign="top"> <td></td> <td>0830</td> <td>Occupational Hazards for Anesthesia Providers</td> <td style="text-align: RIGHT;">K.Domino</td> <td style="text-align: RIGHT;"></td> </tr> <tr valign="top"> <td></td> <td>0930</td> <td colspan="3">Break</td> </tr> <tr class="alt" valign="top"> <td></td> <td>0945</td> <td>Awareness During Anesthesia: Etiology and Prevention</td> <td style="text-align: RIGHT;">K.Domino</td> <td style="text-align: RIGHT;"></td> </tr> <tr valign="top"> <td></td> <td>1045</td> <td>So the Patient Is Rather Overweight: How Does Obesity Impact Our Anesthetic Management?</td> <td style="text-align: RIGHT;">E.Frost</td> <td style="text-align: RIGHT;"></td> </tr> <tr class="alt" valign="top"> <td></td> <td>1145</td> <td>The PACU Nurse Calls: The Patient Is Very Sleepy</td> <td style="text-align: RIGHT;">E.Frost</td> <td style="text-align: RIGHT;"></td> </tr> <tr valign="top"> <td></td> <td>1245</td> <td>Adjourn</td> <td style="text-align: RIGHT;">1.5 Pharmacology</td> <td style="text-align: RIGHT;"></td> </tr> <tr class="alt" valign="top"> <td></td> <td>1330</td> <td colspan="3"><a href="https://www.nwas.com/acls/17acls.html"><strong>Optional ACLS*</strong></a></td> </tr> <tr valign="top"> <td colspan="5"></td> </tr> <tr class="alt" valign="top"> <td colspan="5"> <h4>Sunday, May 14</h4> </td> </tr> <tr valign="top"> <td></td> <td>0700</td> <td colspan="3">Registration - Mandatory Sign In - Continental Breakfast</td> </tr> <tr class="alt" valign="top"> <td></td> <td>0730</td> <td>Anesthesia Closed Claims: New Findings</td> <td style="text-align: RIGHT;">K.Domino</td> <td style="text-align: RIGHT;"></td> </tr> <tr valign="top"> <td></td> <td>0830</td> <td>Closed Claims Project: Lessons for the Ambulatory Provider</td> <td style="text-align: RIGHT;">K.Domino</td> <td style="text-align: RIGHT;"></td> </tr> <tr class="alt" valign="top"> <td></td> <td>0930</td> <td colspan="3">Break</td> </tr> <tr valign="top"> <td></td> <td>0945</td> <td>Adverse Outcomes and Liability of Obstetric Anesthesia</td> <td style="text-align: RIGHT;">K.Domino</td> <td style="text-align: RIGHT;"></td> </tr> <tr class="alt" valign="top"> <td></td> <td>1045</td> <td>Introduction to Nanotechnology: Honey I Shrunk the Kids</td> <td style="text-align: RIGHT;">J.Kryc</td> <td style="text-align: RIGHT;"></td> </tr> <tr class="over" valign="top"> <td></td> <td>1145</td> <td>Nanomedicine: The Cyborg Era Begins</td> <td style="text-align: RIGHT;">J.Kryc</td> <td style="text-align: RIGHT;"></td> </tr> <tr class="alt" valign="top"> <td></td> <td>1245</td> <td>Adjourn</td> <td style="text-align: RIGHT;">1.3 Pharmacology</td> <td style="text-align: RIGHT;"></td> </tr> <tr valign="top"> <td colspan="4"> <div> <p><strong>20 CME I</strong></p> <p><strong>20 Class A CEC</strong></p> <p><strong>7 Pharmacology Hours†</strong></p> </div> </td> <td style="text-align: RIGHT;" width="2%"></td> </tr> </tbody> </table>',
	'<p><strong>Randy Harvey, BS, RRT, CRNA </strong><br /> Chief of Anesthesia and Department Manager<br /> Florida Eye Clinic Ambulatory Surgery Center<br /> Altamonte Springs, Florida<br /> Recipient of the AANA 31st Annual <br /> Alice Magaw Outstanding Clinical Anesthesia Practitioner Award</p> <p><strong>Rainer Lenhardt, MD, MBA</strong><br /> Professor and Vice‑Chair for Clinical Affairs<br /> Department of Anesthesiology and Preoperative Medicine<br /> University of Louisville<br /> Louisville, Kentucky</p> <p><strong>Della Ming Lin, MD</strong><br /> Senior Fellow, Estes Park Institute<br /> Patient Safety Leadership Fellow and Faculty, HRET<br /> Past Department Chief, Anesthesiology<br /> Queen''s Medical Center<br /> Honolulu, Hawaii</p>',
	'<p>This activity has been planned and implemented in accordance with the accreditation requirements and policies of the Accreditation Council for Continuing Medical Education (ACCME) through the joint providership of The A. Webb Roberts Center for Continuing Medical Education of Baylor Scott &amp; White Health and Northwest Anesthesia Seminars. The A. Webb Roberts Center for Continuing Medical Education of Baylor Scott &amp; White Health is accredited by the ACCME to provide continuing medical education for physicians.</p> <p>The A. Webb Roberts Center for Continuing Medical Education of Baylor Scott &amp; White Health designates this live activity for a maximum of 20 <em>AMA PRA Category 1 Credit(s)</em>™. Physicians should claim only the credit commensurate with the extent of their participation in the activity.</p> <p><strong>American Board of Anesthesiology:</strong> CME credit will be sent directly to The American Board of Anesthesiology to fulfill the Part 2 requirement for the Maintenance of Certification in Anesthesiology Program (MOCA®).</p> <p><strong>Anesthesiologist Assistants:</strong> The National Commission for Certification of Anesthesiologist Assistants (NCCAA) will accept CME credit for programs approved for continuing medical education by the AMA, AAPA, and ACCME.</p> <p><strong>Canadian Physicians:</strong> This activity is eligible for Section 1 credits in the Royal College’s Maintenance of Certification (MOC) Program. Participants must log into MAINPORT to claim this activity.</p> <p><strong>Certified Registered Nurse Anesthetists:</strong> This program has been prior approved by the AANA for 20 Class A CEC; AANA Code #1033637; Expiration Date 7/28/2017.</p> <p><strong>Nurse Practitioners:</strong> The American Academy of Nurse Practitioners Certification Program (AANPCP) and the American Nurses Credentialing Center (ANCC) accept CME from organizations accredited by the ACCME.</p> <p><strong>Osteopathic Physicians:</strong> This program is eligible for Category 2 credit with the American Osteopathic Association (AOA).</p> <p><strong>Physician Assistants:</strong> The American Academy of Physician Assistants (AAPA) accepts certificates of participation for educational activities certified for <em>AMA PRA Category 1 Credit™</em> from organizations accredited by ACCME or a recognized state medical society. Physician assistants may receive a maximum of 20 hours of Category I credit for completing this program.</p> <p><strong>Registered Nurses:</strong> NWAS is an approved provider for the following state nursing boards: California Board of Registered Nursing (Provider Number 4833), District of Columbia Board of Nursing (Provider Number 50-7480), Florida Board of Nursing (Provider Number 50-7480), Georgia Board of Nursing (Provider Number 50-7480), and the South Carolina Board of Nursing (Provider Number 50-7480). This program offers 20 contact hours.</p> <p><strong>†Pharmacology hours subject to change.</strong></p>',
	'<p>Northwest Anesthesia Seminars values your patronage and has a simple program to reward you. Our Frequent Attendee Points (FAP) program gives you an added bonus for your tuition dollars spent on our seminars and the money you spend on hotels and cruises booked through our in-house travel agency, Northwest-Worldwide Travel.</p> <p>Here is how it works: Frequent Attendee Points for seminar tuition are calculated at ten percent (10%) of the cost of the seminar. As an example, if your tuition is $800, you would earn 80 Frequent Attendee Points. As an added bonus, you also receive 1.5% of the cost of your net room rate for the hotel or cruise booked through Northwest-Worldwide Travel. For example, if you spend 5 nights at a hotel for $185 per night, would earn you 13.88 Frequent Attendee Points.</p> <p>FAP can only be applied to a full tuition cost; no partial tuition can be credited. There is no dollar value to FAP points for anything other than Northwest Anesthesia Seminars course tuition. FAP points cannot be transferred or gifted to another person.</p>',
	'<p>$100 March 5, 2017 to April 4, 2017. No refund after April 4, 2017; however, 50% of tuition paid may be applied toward tuition for another Northwest Anesthesia Seminars program within the next 12 months. <strong>Cancellation must be made in writing. </strong></p><p>We recommend that you register early to assure your spot. We reserve the right to close registrations to a course at any time without notice. We may not be able to accommodate onsite registrations.</p>'
 FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>conferences_translations` (`id`, `conference_id`, `language_id`, `location_description`, `hotel_description`, `target_audience`, `program_purpose`, `objectives`, `program_schedule`, `faculty`, `accreditation`, `fap`, `course_cancellation_fee`) (SELECT NULL, 2, abbreviation, 'Accusamus aut doloribus enim error fuga impedit neque, praesentium qui, quos ratione sapiente voluptatem.', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Corporis cumque ea incidunt laborum, nulla quasi voluptatibus.', '', '', '', '', '', '', '', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>conferences_translations` (`id`, `conference_id`, `language_id`, `location_description`, `hotel_description`, `target_audience`, `program_purpose`, `objectives`, `program_schedule`, `faculty`, `accreditation`, `fap`, `course_cancellation_fee`) (SELECT NULL, 3, abbreviation, 'Accusamus aut doloribus enim error fuga impedit neque, praesentium qui, quos ratione sapiente voluptatem.', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Corporis cumque ea incidunt laborum, nulla quasi voluptatibus.', '', '', '', '', '', '', '', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>conferences_translations` (`id`, `conference_id`, `language_id`, `location_description`, `hotel_description`, `target_audience`, `program_purpose`, `objectives`, `program_schedule`, `faculty`, `accreditation`, `fap`, `course_cancellation_fee`) (SELECT NULL, 4, abbreviation, 'Accusamus aut doloribus enim error fuga impedit neque, praesentium qui, quos ratione sapiente voluptatem.', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Corporis cumque ea incidunt laborum, nulla quasi voluptatibus.', '', '', '', '', '', '', '', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>conferences_translations` (`id`, `conference_id`, `language_id`, `location_description`, `hotel_description`, `target_audience`, `program_purpose`, `objectives`, `program_schedule`, `faculty`, `accreditation`, `fap`, `course_cancellation_fee`) (SELECT NULL, 5, abbreviation, 'Accusamus aut doloribus enim error fuga impedit neque, praesentium qui, quos ratione sapiente voluptatem.', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Corporis cumque ea incidunt laborum, nulla quasi voluptatibus.', '', '', '', '', '', '', '', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>conferences_translations` (`id`, `conference_id`, `language_id`, `location_description`, `hotel_description`, `target_audience`, `program_purpose`, `objectives`, `program_schedule`, `faculty`, `accreditation`, `fap`, `course_cancellation_fee`) (SELECT NULL, 6, abbreviation, 'Accusamus aut doloribus enim error fuga impedit neque, praesentium qui, quos ratione sapiente voluptatem.', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Corporis cumque ea incidunt laborum, nulla quasi voluptatibus.', '', '', '', '', '', '', '', '' FROM `<DB_PREFIX>languages`);


DROP TABLE IF EXISTS `<DB_PREFIX>conference_participants`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>conference_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conference_id` int(11) unsigned NOT NULL DEFAULT '0',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `speciality_id` int(11) NOT NULL DEFAULT '0',
  `is_admin_registration` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_by_admin_id` int(11) NOT NULL DEFAULT '0',
  `created_date` datetime NULL DEFAULT NULL,
  `booking_number` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `course_registrations` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cc_type` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `cc_holder_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cc_number` varchar(50) CHARACTER SET latin1 NOT NULL,
  `cc_expires_month` varchar(2) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `cc_expires_year` varchar(4) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `cc_cvv_code` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `additional_info` text COLLATE utf8_unicode_ci NOT NULL,
  `email_sent` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


DROP TABLE IF EXISTS `<DB_PREFIX>specialities`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>specialities` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `code` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=36 ;

INSERT INTO `<DB_PREFIX>specialities` (`id`, `code`, `is_active`) VALUES
(1, 'AA', 1),
(2, 'APRN', 1),
(3, 'ARNP', 1),
(4, 'AT', 1),
(5, 'CAA', 1),
(6, 'CCP', 1),
(7, 'CCRN', 1),
(8, 'CEN', 1),
(9, 'CMA', 1),
(10, 'CNM', 1),
(11, 'CNP', 1),
(12, 'CNS', 1),
(13, 'CRNA', 1),
(14, 'CSA', 1),
(15, 'CST', 1),
(16, 'DD', 1),
(17, 'DDS', 1),
(18, 'DNP', 1),
(19, 'DO', 1),
(20, 'EMT', 1),
(21, 'FNP', 1),
(22, 'LPN', 1),
(23, 'MA', 1),
(24, 'MBBS', 1),
(25, 'MD', 1),
(26, 'NP', 1),
(27, 'PA', 1),
(28, 'PA-C', 1),
(29, 'PharmD', 1),
(30, 'RN', 1),
(31, 'RRT', 1),
(32, 'SA', 1),
(33, 'SA-C', 1),
(34, 'SRNA', 1),
(35, 'ST', 1);


DROP TABLE IF EXISTS `<DB_PREFIX>specialities_description`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>specialities_description` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `speciality_id` int(10) NOT NULL DEFAULT '0',
  `language_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=36 ;

INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 1, abbreviation, 'Anesthesiologist Assistant', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 2, abbreviation, 'Advanced Practice Registered Nurse', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 3, abbreviation, 'Advanced Registered Nurse Practitioner', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 4, abbreviation, 'Anesthesia Technologist/Technician', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 5, abbreviation, 'Certified Anesthesiologist Assistant', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 6, abbreviation, 'Certified Clinical Perfusionists', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 7, abbreviation, 'Critical Care Registered Nurse', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 8, abbreviation, 'Certified Emergency Nurse', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 9, abbreviation, 'Certified Medical Assistant', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 10, abbreviation, 'Certified Nurse Midwife', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 11, abbreviation, 'Certified Nurse Practitioner', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 12, abbreviation, 'Clinical Nurse Specialist', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 13, abbreviation, 'Certified Registered Nurse Anesthetist', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 14, abbreviation, 'Certified Surgical Assistant', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 15, abbreviation, 'Certified Surgical Technologist', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 16, abbreviation, 'Doctor of Dentistry', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 17, abbreviation, 'Doctor of Dental Surgery', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 18, abbreviation, 'Doctor of Nursing Practice', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 19, abbreviation, 'Doctor of Osteopathy', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 20, abbreviation, 'Emergency Medical Technician', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 21, abbreviation, 'Family Nurse Practitioner', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 22, abbreviation, 'Licensed Practical Nurse', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 23, abbreviation, 'Medical Assistant', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 24, abbreviation, 'Bachelor of Medicine and Bachelor of Surgery', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 25, abbreviation, 'Doctor of Medicine, Medical Doctor', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 26, abbreviation, 'Nurse Practitioner', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 27, abbreviation, 'Physician Assistant', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 28, abbreviation, 'Physician Assistant-Certified', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 29, abbreviation, 'Doctor of Pharmacy', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 30, abbreviation, 'Registered Nurse', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 31, abbreviation, 'Registered Respiratory Therapist', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 32, abbreviation, 'Surgical Assistant', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 33, abbreviation, 'Surgical Assistant - Certified', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 34, abbreviation, 'Student Registered Nurse Anesthetist', '' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>specialities_description` (`id`, `speciality_id`, `language_id`, `name`, `description`) (SELECT NULL, 35, abbreviation, 'Surgical Technologist', '' FROM `<DB_PREFIX>languages`);


DROP TABLE IF EXISTS `<DB_PREFIX>conference_tuitions`;
CREATE TABLE IF NOT EXISTS `<DB_PREFIX>conference_tuitions` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `conference_id` smallint(6) unsigned NOT NULL DEFAULT '0',
  `name` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `price_type_1` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price_type_2` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price_type_3` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

INSERT INTO `<DB_PREFIX>conference_tuitions` (`id`, `conference_id`, `name`, `price_type_1`, `price_type_2`, `price_type_3`) VALUES
(1, 1, 'Early Payment', '950.00', '900.00', '800.00'),
(2, 1, 'Daily: 1st Day Attending', '255.00', '243.00', '218.00'),
(3, 1, 'Additional Day(s)', '255.00', '243.00', '218.00'),
(4, 2, 'Early Payment', '950.00', '900.00', '800.00'),
(5, 2, 'Daily: 1st Day Attending', '255.00', '243.00', '218.00'),
(6, 2, 'Additional Day(s)', '255.00', '243.00', '218.00'),
(7, 3, 'Early Payment', '900.00', '870.00', '760.00'),
(8, 3, 'Daily: 1st Day Attending', '255.00', '243.00', '218.00'),
(9, 3, 'Additional Day(s)', '322.00', '303.00', '298.00'),
(10, 4, 'Early Payment', '750.00', '650.00', '600.00'),
(11, 4, 'Daily: 1st Day Attending', '200.00', '195.00', '185.00'),
(12, 4, 'Additional Day(s)', '200.00', '190.00', '180.00');

INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCES', 'Conferences' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCE_SETTINGS', 'Conference Settings' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MD_CONFERENCES', 'The Conference Management module provides a web based solution for managing delegate registration, online payments, event planning and creation of the detailed agenda.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCE_NAME', 'Conference Name' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCE_TYPES', 'Conference Types' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCE_TYPE', 'Conference Type' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCE_PARTICIPANTS', 'Conference Participants' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PARTICIPANTS', 'Participants' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCES_MANAGEMENT', 'Conferences Management' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FULL_DESCRIPTION', 'Full Description' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_WORKSHOP_DATES', 'Workshop Dates' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCE_REGESTRATION_LINK', 'Click here for course and hotel registration' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MSN_CONFERENCES_IS_ACTIVE', 'Activate Conferences' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_MS_CONFERENCES_IS_ACTIVE', 'Defines whether Conferences Module is active or not' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_SPECIALTY', 'Specialty' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_I_AM_A', 'I am a' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_COURSE_CANCELLATION_FEE', 'Course Cancellation Fee' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCES_SELECT_OPTION_ALERT', 'Please select at least one option.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_COURSE_REGISTRATION', 'Course Registration' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCES_SELECT_COURSE_ALERT', 'Please select at least one course.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_SPECIALITIES_MANAGEMENT', 'Specialities Management' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_OTHER_NOT_LISTED', 'Other - Not Listed' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFIRM_EMAIL_ADDRESS', 'Confirm E-mail address' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONF_EMAIL_MATCH', 'Email address must be match with Confirm Email' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFIRM_EMAIL_EMPTY_ALERT', 'Confirm Email cannot be empty! Please re-enter.' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_LOCATION_DESCRIPTION', 'Location Description' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TARGET_AUDIENCE', 'Target Audience' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PROGRAM_PURPOSE', 'Program Purpose' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_OBJECTIVES', 'Objectives' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PROGRAM_SCHEDULE', 'Program Schedule' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FACULTY', 'Faculty' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_ACCREDITATION', 'Accreditation' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_FAP', 'Frequent Attendee Points' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_ACCOMMODATIONS', 'Accommodations' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_PROGRAM', 'Program' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCE_PRICE_TYPE_1', 'Price for MD/DO' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCE_PRICE_TYPE_2', 'Price for CRNA/PA/AA/ARNP' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCE_PRICE_TYPE_3', 'Price for Resident/RN/Other' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCE_TYPE_1', 'MD/DO' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCE_TYPE_2', 'CRNA/PA/AA/ARNP' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCE_TYPE_3', 'Resident/RN/Other' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_CONFERENCE_TUITIONS', 'Conference Tuitions' FROM `<DB_PREFIX>languages`);
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) (SELECT NULL, abbreviation, '_TUITIONS', 'Tuitions' FROM `<DB_PREFIX>languages`);
