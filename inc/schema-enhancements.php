<?php
/* ---------------------------------------------------------------------------
 * Schema Markup Enhancements using RankMath
 * Currently it supports the following schema sets:
 *	 * Person
 *   * FAQPage
 *
 * Still to do:
 *	* Job
 * 	* Event
 *	* Course
 *
 * --------------------------------------------------------------------------- */
class SchemaEnhancements {

	 public function __construct() {
		 add_filter('rank_math/json_ld', array($this, 'enhance_json_ld'), 10, 2);
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
				 $data['personSchema'] = [
					 '@type' => 'Person',
				 ];

				 if ($hasPersonTitle) {
					 $data['personSchema']['jobTitle'] = $hasPersonTitle;
				 }

				 if ($hasPersonhonorificPrefix) {
					  $data['personSchema']['honorificPrefix'] = $hasPersonhonorificPrefix;
				  }

				  if ($hasPersonhonorificSuffix) {
					   $data['personSchema']['honorificSuffix'] = $hasPersonhonorificSuffix;
				   }

				 if ($hasPersonKnowsAbout) {
					 $data['personSchema']['knowsAbout'] = [$hasPersonKnowsAbout];
				 }

				 if ($hasPersonAlumniOf) {
					 $data['personSchema']['alumniOf'] = [];

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

						 $data['personSchema']['alumniOf'][] = $alumniOf_item;
					 }
				 }

				 if ($hasPersonAwards) {
                    $awards_list = [];

                    while (have_rows('PersonAwards', 'user_' . $author_id)) {
                        the_row();
                        $awards_list[] = get_sub_field('PersonAward');
                    }

                    if (!empty($awards_list)) {
                        $data['personSchema']['awards'] = $awards_list;
                    }
                }
			 }
		 }

		 $has_faqs = have_rows('frequently_asked_questions');
		 if ($has_faqs && !is_author()) {
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

		 return $data;
	 }
 }
