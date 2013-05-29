<?php
/**
 * ITransactionService.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 5.5.13 22:31
 */

namespace ITILSimulator\Services;

/**
 * Interface for services supporting Unit Of Work pattern and database commits of pending changes
 * @package ITILSimulator\Services
 */
interface ITransactionService
{
	function commitChanges();
}