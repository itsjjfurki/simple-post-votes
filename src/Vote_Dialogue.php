<?php
/**
 * @package simple-post-votes-plugin
 */

namespace Simple_Post_Votes;

/**
 * Class Vote_Dialogue for placing UI for voting to posts page in the frontend
 */
class Vote_Dialogue {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Call the initialization method to set up necessary configurations or actions.
		$this->init();
	}

	/**
	 * Injects content
	 */
	private function init() {
		// Add a filter to modify the content before displaying it.
		add_filter( 'the_content', array( $this, 'build_vote_dialogue' ) );
	}

	/**
	 * Builds vote dialogue
	 *
	 * @param mixed $content existing post content.
	 * @return mixed
	 */
	public function build_vote_dialogue( mixed $content ): string {
		// Check if viewing a single post.
		if ( is_single() ) {
			// Construct the custom voting dialogue HTML.
			$custom_div =
			'<div class="spv-container">
                <div class="spv-row">
                    <div class="spv-message">' . __( 'WAS THIS ARTICLE HELPFUL', 'simple-post-votes' ) . '?</div>
                    <div class="spv-buttons">
                        <div class="spv-button-group">
                            <input type="radio" id="spv-vote-btn-yes" name="spv-vote-btn" value="yes">
                            <label for="spv-vote-btn-yes">
                                <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><path d="m16 2a14 14 0 1 0 14 14 14 14 0 0 0 -14-14zm-4.5 9a2.5 2.5 0 1 1 -2.5 2.5 2.48 2.48 0 0 1 2.5-2.5zm4.5 13a8 8 0 0 1 -6.85-3.89l1.71-1a6 6 0 0 0 10.28 0l1.71 1a8 8 0 0 1 -6.85 3.89zm4.5-8a2.5 2.5 0 1 1 2.5-2.5 2.48 2.48 0 0 1 -2.5 2.5z"/><path d="m0 0h32v32h-32z" fill="none"/></svg>
                                <span>' . __( 'yes', 'simple-post-votes' ) . '</span>
                            </label>
                            <input type="radio" id="spv-vote-btn-no" name="spv-vote-btn" value="no">
                            <label for="spv-vote-btn-no">
                                <svg viewBox="0 0 17 17" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zM4.5 6a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1h-2zm5 0a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1h-2zm-5 4a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7z"/></svg>
                                <span>' . __( 'no', 'simple-post-votes' ) . '</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>';

			// Append the custom dialogue HTML to the post content.
			$content .= $custom_div;
		}

		// Return the modified content.
		return $content;
	}
}
