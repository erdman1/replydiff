<?php
/**
 *
 * Reply in Different Topic. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, Erd Man, https://github.com/erdman1/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace erdman\replydiff\mcp;

/**
 * Reply in Different Topic MCP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Main MCP module
	 *
	 * @param int    $id   The module ID
	 * @param string $mode The module mode (for example: manage or settings)
	 * @throws \Exception
	 */


	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \erdman\replydiff\controller\mcp_controller $mcp_controller */
		$mcp_controller = $phpbb_container->get('erdman.replydiff.controller.mcp');

		// Load a template for our MCP page
		$this->tpl_name = 'mcp_replydiff_body';

		// Set the page title for our MCP page
		$this->page_title = 'MCP_REPLYDIFF_TITLE';

		// Make the $u_action url available in our MCP controller
		$mcp_controller->set_page_url($this->u_action);

		
		// Load the display options handle in our MCP controller
		$mcp_controller->display_options();
	}
}