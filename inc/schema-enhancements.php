<?php
/* ---------------------------------------------------------------------------
 * Schema Markup Enhancements using RankMath
 * Currently it supports the following schema sets:
 *	* Person
 *	* FAQPage
 *	* Job Posting
 *	* Event
 *
 * Still to do:
 *	* Course
 *
 * --------------------------------------------------------------------------- */
class SchemaEnhancements {

	public function __construct() {
		add_filter('rank_math/json_ld', array($this, 'enhance_json_ld'), 99, 2);
	}

	public function enhance_json_ld($data, $jsonld) {
		// Check for an author page and set $author_id
		if ((is_single() || is_page() || is_singular('academy')) || is_author()) {

			if (is_single() || is_page() || is_singular('academy')) {
				$author_id = get_post_field('post_author', get_the_ID());
			}

			// On an author archive page
			elseif (is_author()) {
				$author_id = get_queried_object_id();
			}

			$hasPersonTitle = get_field('PersonTitle', 'user_' . $author_id);
			$hasPersonhonorificPrefix = get_field('PersonhonorificPrefix', 'user_' . $author_id);
			$hasPersonhonorificSuffix = get_field('PersonhonorificSuffix', 'user_' . $author_id);
			$hasPersonKnowsAbout = get_field('PersonKnowsAbout', 'user_' . $author_id);
			$hasPersonAlumniOf = have_rows('PersonAlumniOf', 'user_' . $author_id);
			$hasPersonAwards = have_rows('PersonAwards', 'user_' . $author_id);

			if ($hasPersonTitle || $hasPersonhonorificPrefix || $hasPersonhonorificSuffix || $hasPersonKnowsAbout || $hasPersonAlumniOf || $hasPersonAwards) {

				if ($hasPersonTitle) {
					$data['ProfilePage']['jobTitle'] = $hasPersonTitle;
				}

				if ($hasPersonhonorificPrefix) {
					$data['ProfilePage']['honorificPrefix'] = $hasPersonhonorificPrefix;
				}

				if ($hasPersonhonorificSuffix) {
					$data['ProfilePage']['honorificSuffix'] = $hasPersonhonorificSuffix;
				}

				if ($hasPersonKnowsAbout) {
					$data['ProfilePage']['knowsAbout'] = $hasPersonKnowsAbout;
				}

				if ($hasPersonAlumniOf) {
					$data['ProfilePage']['alumniOf'] = [];

					while (have_rows('PersonAlumniOf', 'user_' . $author_id)) {
						the_row();
						$alumniOf_item = [
							'@type' => get_sub_field('PersonAlumniOftype'),
							'name' => get_sub_field('PersonAlumniOfname'),
						];

						$sameAs_text = get_sub_field('PersonAlumniOfsameAs');
						if (!empty($sameAs_text)) {
							$sameAs_urls = array_filter(array_map('trim', explode("\n", $sameAs_text)));
							if (!empty($sameAs_urls)) {
								$alumniOf_item['sameAs'] = $sameAs_urls;
							}
						}

						$data['ProfilePage']['alumniOf'][] = $alumniOf_item;
					}
				}

				if ($hasPersonAwards) {
					$awards_list = [];

					while (have_rows('PersonAwards', 'user_' . $author_id)) {
						the_row();
						$awards_list[] = get_sub_field('PersonAward');
					}

					if (!empty($awards_list)) {
						$data['ProfilePage']['awards'] = $awards_list;
					}
				}
			}
		}

		if (have_rows('frequently_asked_questions') && !is_author()) {
			$data['faqs'] = [
				'@type' => 'FAQPage',
			];

			while (have_rows('frequently_asked_questions')) {
				the_row();

				$accepted_answer = [
					'@type' => 'Answer',
					'text' => esc_attr(get_sub_field('faq_answer')),
				];

				if (get_sub_field('faq_image')) {
					$accepted_answer['image'] = [
						'@type' => 'ImageObject',
						'contentUrl' => get_sub_field('faq_image')['url'],
					];
				}

				$data['faqs']['mainEntity'][] = [
					'@type' => 'Question',
					'name' => esc_attr(get_sub_field('faq_question')),
					'acceptedAnswer' => $accepted_answer,
				];
			}
		}

		if (get_field('create_new_job_listing') === 'Yes' && have_rows('job_listing') && !is_author()) {
			$data['jobs'] = [];

			while (have_rows('job_listing')) {
				the_row();
				$job = [
					'@type' => 'JobPosting',
					'title' => get_sub_field('job_title'),
					'description' => get_sub_field('job_description'),
					'qualifications' => get_sub_field('job_qualifications'),
					'benefits' => get_sub_field('job_benefits'),
					'identifier' => get_sub_field('job_identifier'),
					'datePosted' => get_sub_field('job_dateposted'),
					'validThrough' => get_sub_field('job_posting_validthrough'),
					'employmentType' => get_sub_field('job_employementtype'),
					'hiringOrganization' => [
						'@type' => 'Organization',
						'name' => get_sub_field('job_hiringorganizationname'),
						'sameAs' => get_sub_field('job_hiringorganization_sameas'),
						'logo' => get_sub_field('job_hiringorganization_logo')['url']
					],
					// Add baseSalary details
					'baseSalary' => [
						'@type' => 'MonetaryAmount',
						'currency' => get_sub_field('job_basesalary_currency'),
						'value' => [
							'@type' => 'QuantitativeValue',
							'value' => get_sub_field('job_basesalary_value'),
							'unitText' => get_sub_field('job_basesalary_unittext')
						]
					]
				];

				$locationType = get_sub_field('job_location_type');
				$applicantLocationRequirements = get_sub_field('job_applicantlocationrequirements');

				if ($locationType === 'TELECOMMUTE') {
					$job['jobLocationType'] = 'TELECOMMUTE';

					// Check if default to the country of jobLocation is selected
					if (in_array('--default to the country of a jobLocation--', $applicantLocationRequirements)) {
						// Include address details
						$job['jobLocation'] = [
							'@type' => 'Place',
							'address' => [
								'@type' => 'PostalAddress',
								'streetAddress' => get_sub_field('job_location_streetaddress'),
								'addressLocality' => get_sub_field('job_location_locality'),
								'addressRegion' => get_sub_field('job_location_region'),
								'postalCode' => get_sub_field('job_location_postalcode'),
								'addressCountry' => get_sub_field('job_location_country')
							]
						];

						// Include coordinates and hasMap if provided
						$latitude = get_sub_field('job_location_latitude');
						$longitude = get_sub_field('job_location_longitude');
						if (!empty($latitude) && !empty($longitude)) {
							$job['jobLocation']['geo'] = [
								'@type' => 'GeoCoordinates',
								'latitude' => $latitude,
								'longitude' => $longitude
							];
						}

						$hasMap = get_sub_field('job_location_hasmap');
						if (!empty($hasMap)) {
							$job['jobLocation']['hasMap'] = $hasMap;
						}

						// Set applicantLocationRequirements to the job location country
						$job['applicantLocationRequirements'] = [get_sub_field('job_location_country')];
					} else {
						// Set applicantLocationRequirements to selected countries
						$job['applicantLocationRequirements'] = $applicantLocationRequirements;
					}
				} else if ($locationType === 'Place') {
					// Include all address details, coordinates, and hasMap
						$job['jobLocation'] = [
						'@type' => 'Place',
						'address' => [
							'@type' => 'PostalAddress',
							'streetAddress' => get_sub_field('job_location_streetaddress'),
							'addressLocality' => get_sub_field('job_location_locality'),
							'addressRegion' => get_sub_field('job_location_region'),
							'postalCode' => get_sub_field('job_location_postalcode'),
							'addressCountry' => get_sub_field('job_location_country')
						]
					];

					// Include coordinates and hasMap if provided
					$latitude = get_sub_field('job_location_latitude');
					$longitude = get_sub_field('job_location_longitude');
					if (!empty($latitude) && !empty($longitude)) {
						$job['jobLocation']['geo'] = [
							'@type' => 'GeoCoordinates',
							'latitude' => $latitude,
							'longitude' => $longitude
						];
					}

					$hasMap = get_sub_field('job_location_hasmap');
					if (!empty($hasMap)) {
						$job['jobLocation']['hasMap'] = $hasMap;
					}
				}

				// Add the job to the jobs array
				$data['jobs'][] = $job;
			}
		}

		if (get_field('create_new_event_listing') === 'Yes' && have_rows('event_listing') && !is_author()) {
			$data['events'] = [];

			while (have_rows('event_listing')) {
				the_row();
				$event = [
					'@type' => 'Event',
					'name' => get_sub_field('event_name'),
					'startDate' => get_sub_field('event_start_date'),
					'endDate' => get_sub_field('event_end_date'),
					'description' => get_sub_field('event_description'),
					'eventAttendanceMode' => get_sub_field('event_attendance_mode'),
					'location' => [],
					'image' => []
				];

				// Event Status
				$eventStatus = get_sub_field('event_status');
				if (!empty($eventStatus)) {
					$event['eventStatus'] = 'https://schema.org/' . $eventStatus;
				}

				// Event Location
				$eventLocationType = get_sub_field('event_location');
				if ($eventLocationType === 'Physical location') {
					$event['location'] = [
						'@type' => 'Place',
						'address' => [
							'@type' => 'PostalAddress',
							'streetAddress' => get_sub_field('event_location_streetaddress'),
							'addressLocality' => get_sub_field('event_location_locality'),
							'addressRegion' => get_sub_field('event_location_region'),
							'postalCode' => get_sub_field('event_location_postalcode'),
							'addressCountry' => get_sub_field('event_location_country')
						],
						'geo' => [
							'@type' => 'GeoCoordinates',
							'latitude' => get_sub_field('event_location_latitude'),
							'longitude' => get_sub_field('event_location_longitude')
						],
						'url' => get_sub_field('event_location_hasmap')
					];

					// Event Location Name
					$locationName = get_sub_field('event_location_name');
					if (!empty($locationName)) {
						$event['location']['name'] = $locationName;
					}
				} else if ($eventLocationType === 'Online event') {
					$event['location'] = [
						'@type' => 'VirtualLocation',
						'url' => get_sub_field('event_location_url')
					];
				}

				// Event Images
				if (have_rows('event_image')) {
					while (have_rows('event_image')) {
						the_row();
						$image = get_sub_field('event_image_repeater');
						if ($image) {
							$event['image'][] = [
								'@type' => 'ImageObject',
								'url' => $image['url']
							];
						}
					}
				}

				// Event Organizer
				$organizerType = get_sub_field('event_organizer');
				if ($organizerType === 'Person' || $organizerType === 'Organization') {
					$event['organizer'] = [
						'@type' => $organizerType,
						'name' => get_sub_field('event_organizer_name'),
						'url' => get_sub_field('event_organizer_url')
					];
				}

				// Event Performer
				$performerType = get_sub_field('event_performer');
				if ($performerType === 'Person') {
					$event['performer'] = [];
					if (have_rows('event_performer_person')) {
						while (have_rows('event_performer_person')) {
							the_row();
							$event['performer'][] = [
								'@type' => 'Person',
								'name' => get_sub_field('even_performer_person_name')
							];
						}
					}
				} elseif ($performerType === 'PerformingGroup') {
					$performingGroupName = get_sub_field('event_performing_group_name');
					if (!empty($performingGroupName)) {
						$event['performer'] = [
							'@type' => 'PerformingGroup',
							'name' => $performingGroupName
						];
					}
				}

				// Event Offers
				if (get_sub_field('event_offer') === 'Yes' && have_rows('event_offers')) {
					while (have_rows('event_offers')) {
						the_row();
						$offer = [
							'@type' => 'Offer',
							'availability' => 'http://schema.org/' . get_sub_field('event_offers_availability'),
							'price' => get_sub_field('event_offers_price'),
							'priceCurrency' => get_sub_field('event_offers_pricecurrency'),
							'url' => get_sub_field('event_offers_url')
						];

						$validFrom = get_sub_field('event_offers_validfrom');
						if ($validFrom) {
							$offer['validFrom'] = $validFrom;
						}

						$event['offers'][] = $offer;
					}
				}

				// Add the event to the events array
				$data['events'][] = $event;
			}
		}

		return $data;
	}
 }
