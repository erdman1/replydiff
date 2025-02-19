<?php
/**
 *
 * Reply in Different Topic. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, Erd Man, https://github.com/erdman1/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace erdman\replydiff\migrations;

class install_mcp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT module_id
			FROM ' . $this->table_prefix . "modules
			WHERE module_class = 'mcp'
				AND module_langname = 'MCP_REPLYDIFF_TITLE'";
		$result = $this->db->sql_query($sql);
		$module_id = $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id !== false;
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v320\v320'];
	}

	public function update_data()
	{
		return [
			['module.add', [
				'mcp',
				0,
				'MCP_REPLYDIFF_TITLE'
			]],
			['module.add', [
				'mcp',
				'MCP_REPLYDIFF_TITLE',
				[
					'module_basename'	=> '\erdman\replydiff\mcp\main_module',
					'modes'				=> ['front'],
				],
			]],
		];
	}
}
