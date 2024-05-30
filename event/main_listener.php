<?php
/**
 *
 * Reply in Different Topic. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, Erd Man, https://github.com/erdman1/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace erdman\replydiff\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\db\driver\driver_interface;

/**
 * Reply in Different Topic Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup' => 'load_language_on_setup',
			//'core.page_header' => 'add_page_header_link',
			//'core.viewonline_overwrite_location' => 'viewonline_page',
			//'core.display_forums_modify_template_vars' => 'display_forums_modify_template_vars',
			//'core.submit_post_end' => 'handle_post_submission',
			'core.posting_modify_message_text' => 'modify_destination',
			'core.posting_modify_post_data' => 'show_selection'
		];
	}

	/* @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/** @var string phpEx */
	protected $php_ext;

	protected $db;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language	$language	Language object
	 * @param \phpbb\controller\helper	$helper		Controller helper object
	 * @param \phpbb\template\template	$template	Template object
	 * @param string                    $php_ext    phpEx
	 */
	public function __construct(\phpbb\language\language $language, \phpbb\controller\helper $helper, \phpbb\template\template $template, $php_ext, driver_interface $db)
	{
		$this->language = $language;
		$this->helper = $helper;
		$this->template = $template;
		$this->php_ext = $php_ext;
		$this->db = $db;
	}

	/**
	 * Load common language files during user setup
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'erdman/replydiff',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Add a link to the controller in the forum navbar
	 */
	public function add_page_header_link()
	{
		$this->template->assign_vars([
			'U_REPLYDIFF_PAGE' => $this->helper->route('erdman_replydiff_controller', ['name' => 'world']),
		]);
	}

	/**
	 * Show users viewing Reply in Different Topic page on the Who Is Online page
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function viewonline_page($event)
	{
		if ($event['on_page'][1] === 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/demo') === 0) {
			$event['location'] = $this->language->lang('VIEWING_ERDMAN_REPLYDIFF');
			$event['location_url'] = $this->helper->route('erdman_replydiff_controller', ['name' => 'world']);
		}
	}

	/**
	 * A sample PHP event
	 * Modifies the names of the forums on index
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function display_forums_modify_template_vars($event)
	{
		$forum_row = $event['forum_row'];
		$forum_row['FORUM_NAME'] .= $this->language->lang('REPLYDIFF_EVENT');
		$event['forum_row'] = $forum_row;
	}


	public function handle_reply_redirection($event)
	{
		$post_data = $event['post_data'];

		// Check if replydiff_id is set and greater than 0
		$replydiff_id = isset($post_data['replydiff_id']) ? (int) $post_data['replydiff_id'] : 0;

		if ($replydiff_id > 0) {
			// For testing: Echo a message indicating a redirection is intended
			echo "Redirect to topic with ID: " . $replydiff_id;
		} else {
			echo "Normal reply process";
		}
	}


	
public function show_selection($event){
	$post_data = $event->get_data();
	global $request;
	if (
		isset($post_data['mode']) && $post_data['mode'] === 'quote' &&
		isset($post_data['post_data']['replydiff_id']) && $post_data['post_data']['replydiff_id'] > 0){
		$this->template->assign_vars(array(
			'S_SHOW_SISTER_THREAD_OPTION' => true,
		));
	} else {
		$this->template->assign_vars(array(
			'S_SHOW_SISTER_THREAD_OPTION' => false,
		));
	}


}
	public function modify_destination($event)
	{
		$post_data = $event->get_data();

		global $request;
	
		// Check if the mode is 'quote' and 'replydiff_id' is set and greater than 0
		if (
			isset($post_data['mode']) && $post_data['mode'] === 'quote' &&
			isset($post_data['post_data']['replydiff_id']) && $post_data['post_data']['replydiff_id'] > 0
		) {
			// Check the radio button selection
			$post_destination = $request->variable('post_destination', 'current_thread');

			if ($post_destination === 'sister_thread') {
				// Get the replydiff_id
				$replydiff_id = $post_data['post_data']['replydiff_id'];
				// Change the topic_id to replydiff_id
				$post_data['topic_id'] = $replydiff_id;
			}

			// Update the event data
			$event->set_data($post_data);
		}
	}



	public function handle_post_submission($event)
	{
		global $db, $request;

		// Get the replydiff_id from the form submission
		$replydiff_id = $request->variable('replydiff_id', '');
		$topic_id = $event['data']['topic_id'];

		// Check if replydiff_id is numeric and greater than 0
		if (is_numeric($replydiff_id) && $replydiff_id > 0) {
			$sql = 'SELECT topic_id FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . (int) $replydiff_id;
			$result = $db->sql_query($sql);
			$valid_topic_id = $db->sql_fetchfield('topic_id');
			$db->sql_freeresult($result);

			// If it's a valid topic ID, update the topics table
			if ($valid_topic_id) {
				$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET replydiff_id = ' . (int) $replydiff_id . '
						WHERE topic_id = ' . (int) $topic_id;
				$db->sql_query($sql);
			}
		} elseif ($replydiff_id === '0') {
			// Set replydiff_id to 0 in the database
			$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET replydiff_id = 0
					WHERE topic_id = ' . (int) $topic_id;
			$db->sql_query($sql);
		}
		// If replydiff_id is empty, do nothing
	}

}
