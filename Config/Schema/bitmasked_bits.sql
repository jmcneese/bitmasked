-- @package     Bitmasked
-- @subpackage  Bitmasked.Config.Schema
-- @author      Joshua McNeese <jmcneese@gmail.com>
-- @license		Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
-- @copyright	Copyright (c) 2009-2012 Joshua M. McNeese, Curtis J. Beeson
CREATE TABLE `bitmasked_bits` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `model` varchar(32) NOT NULL,
  `foreign_id` int(11) unsigned NOT NULL,
  `bits` bigint(20) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `polymorphic_idx` (`model`,`foreign_id`)
);