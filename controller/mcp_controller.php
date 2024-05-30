<?php
/**
 *
 * Reply in Different Topic. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, Erd Man, https://github.com/erdman1/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace erdman\replydiff\controller;

/**
 * Reply in Different Topic MCP controller.
 */
class mcp_controller
{
	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;
	protected $topic_id;

	/** @var string Custom form action */
	protected $u_action;

	protected $db;


	/**
	 * Constructor.
	 *
	 * @param \phpbb\language\language		$language	Language object
	 * @param \phpbb\request\request		$request	Request object
	 * @param \phpbb\template\template		$template	Template object
	 * @param \phpbb\db\driver\driver_interface $db Database object
	 */
	public function __construct(\phpbb\language\language $language, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\db\driver\driver_interface $db)
	{
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->db = $db;
	}

	/**
	 * Display the options a moderator can take for this extension.
	 *
	 * @return void
	 */

	public function display_form()
	{
		add_form_key('erdman_replydiff_mcp');
		$this->topic_id = $this->request->variable('t', 0);
		$sql = 'SELECT topic_title, replydiff_id FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . (int) $this->topic_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row) {
			$current_topic_title = $row['topic_title'];
			$replydiff_id = $row['replydiff_id'];
		} else {
			$current_topic_title = 0;
			$replydiff_id = 0; 
		}


		// Fetch the title of the redirect topic if replydiff_id is set
		$redirect_topic_title = '';
		if (!empty($replydiff_id)) {
			$sql = 'SELECT topic_title FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . (int) $replydiff_id;
			$result = $this->db->sql_query($sql);
			$redirect_topic_title = $this->db->sql_fetchfield('topic_title');
			$this->db->sql_freeresult($result);
		}

		$this->template->assign_vars([
			'CURRENT_TOPIC_ID' => $this->topic_id,
			'CURRENT_TOPIC_TITLE' => $current_topic_title,
			'REDIRECT_TOPIC_TITLE' => $redirect_topic_title,
		]);
	}


	public function display_options()
	{
		// Handle form submission
		$this->handle_form_submission();

		// Display form
		$this->display_form();

	}

	public function handle_form_submission()
	{
		if ($this->request->is_set_post('submit')) {
			$errors = [];
			if ($this->request->is_set_post('submit')) {
				// Test if the submitted form is valid
				if (!check_form_key('erdman_replydiff_mcp')) {
					$errors[] = $this->language->lang('FORM_INVALID');
				}

				// Proceed only if there are no errors
				if (empty($errors)) {
					// Retrieve current_id from the form
					$current_id = $this->request->variable('current_id', 0);
					$this->topic_id = $this->request->variable('current_id', 0);
					$replydiff_id = $this->request->variable('replydiff_id', '');

					// Validate and process the replydiff_id
					if ($replydiff_id !== '') {
						$replydiff_id = (int) $replydiff_id;

						// Check if the replydiff_id is a valid topic ID (if greater than 0)
						if ($replydiff_id > 0) {
							$sql = 'SELECT 1 FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . $replydiff_id;
							$result = $this->db->sql_query($sql);
							$is_valid_topic_id = $this->db->sql_fetchfield('1');
							$this->db->sql_freeresult($result);

							if (!$is_valid_topic_id) {
								$errors[] = $this->language->lang('INVALID_TOPIC_ID');
								$replydiff_id = 0;
							}
						}

						// Update replydiff_id in the database
						if (empty($errors)) {
							$sql = 'UPDATE ' . TOPICS_TABLE . '
								SET replydiff_id = ' . $replydiff_id . '
								WHERE topic_id = ' . $current_id;
							$this->db->sql_query($sql);
							if (empty($errors)) {
								$sql = 'SELECT replydiff_id FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . (int) $current_id;
								$result = $this->db->sql_query($sql);
								$updated_replydiff_id = $this->db->sql_fetchfield('replydiff_id');
								$this->db->sql_freeresult($result);

								// Assign updated values to the template
								$this->template->assign_vars([
									'SUCCESS_MSG' => $this->language->lang('MCP_REPLYDIFF_UPDATE_SUCCESS'),
									'CURRENT_REPLYDIFF_ID' => $updated_replydiff_id,
									'CURRENT_TOPIC_ID' => $this->topic_id,
								]);
							}
						}
					}
				}
			}
		}

	}

		
	/**
	 * Set custom form action.
	 *
	 * @param string	$u_action	Custom form action
	 * @return void
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
