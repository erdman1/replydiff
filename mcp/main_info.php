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
 * Reply in Different Topic MCP module info.
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\erdman\replydiff\mcp\main_module',
			'title'		=> 'MCP_REPLYDIFF_TITLE',
			'modes'		=> [
				'front'	=> [
					'title'	=> 'MCP_REPLYDIFF',
					'auth'	=> 'ext_erdman/replydiff',
					'cat'	=> ['MCP_REPLYDIFF_TITLE'],
				],
			],
		];
	}
}
