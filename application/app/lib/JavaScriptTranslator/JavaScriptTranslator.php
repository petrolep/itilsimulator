<?php
/**
 * JavaScriptTranslator.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 28.4.13 16:11
 */

namespace ITILSimulator\Base\JavaScriptTranslator;

use ITILSimulator\Runtime\Events\EventTypeEnum;
use ITILSimulator\Runtime\RuntimeContext\JavaScriptTranslatorConfig;
use \ParseNode;
use \JParser;

/**
 * Simple translator to transform pseudo-JavaScript code to PHP code, which can be executed by PHP interpreter.
 * Uses JavaScript lexical and syntactic analyser, however allows and transforms only subset of regular JavaScript syntax.
 * @package ITILSimulator\Base\JavaScriptTranslator
 */
class JavaScriptTranslator
{
	#region "Configuration"

	/**
	 * Map of transformed variables to custom objects.
	 * E.g. "this" in JavaScript is transformed to $_context in PHP.
	 * @var array
	 */
	protected $contextVariables = array(
		'this' => '$_context',
		'context' => '$_context',
		'Math' => '$_helpers->math',
		'Date' => '$_helpers->date',
	);

	/**
	 * Map of predefined variables and their static properties.
	 * @var array
	 */
	protected $staticContextVariables = array(
		'eventTypeEnum' => array(
			'CONFIGURATION_ITEM_RESTARTED' => '\ITILSimulator\Runtime\Events\EventTypeEnum::RUNTIME_CONFIGURATION_ITEM_RESTARTED',
			'CONFIGURATION_ITEM_REPLACED' => '\ITILSimulator\Runtime\Events\EventTypeEnum::RUNTIME_CONFIGURATION_ITEM_REPLACED',
			'EVENT_ARCHIVED' => '\ITILSimulator\Runtime\Events\EventTypeEnum::RUNTIME_EVENT_ARCHIVED',
			'INCIDENT_FIX_APPLIED' => '\ITILSimulator\Runtime\Events\EventTypeEnum::RUNTIME_INCIDENT_FIX_APPLIED',
			'INCIDENT_WORKAROUND_APPLIED' => '\ITILSimulator\Runtime\Events\EventTypeEnum::RUNTIME_INCIDENT_WORKAROUND_APPLIED',
			'INCIDENT_ESCALATED' => '\ITILSimulator\Runtime\Events\EventTypeEnum::RUNTIME_INCIDENT_ESCALATED',
			'INCIDENT_CLOSED' => '\ITILSimulator\Runtime\Events\EventTypeEnum::RUNTIME_INCIDENT_CLOSED',
			'PROBLEM_RFC_REQUESTED' => '\ITILSimulator\Runtime\Events\EventTypeEnum::RUNTIME_PROBLEM_RFC_REQUESTED',
			'PROBLEM_KNOWN_ERROR_CREATED' => '\ITILSimulator\Runtime\Events\EventTypeEnum::RUNTIME_PROBLEM_KNOWN_ERROR_REQUESTED',
			'MESSAGE_ACCEPTED' => '\ITILSimulator\Runtime\Events\EventTypeEnum::ACTIVITY_MESSAGE_ACCEPTED',
		)
	);

	/**
	 * Map of disabled variables (PHP superglobals).
	 * Blacklist.
	 * @var array
	 */
	protected $disabledVariables = array('GLOBALS', '_SERVER', '_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_REQUEST', '_ENV');

	/**
	 * Map of allowed PHP functions.
	 * Whitelist.
	 * @var array
	 */
	protected $allowedFunctions = array('array');

	#endregion

	#region "Public methods"

	/**
	 * Transforms the input code in JavaScript to corresponding code in PHP.
	 * @param $source
	 * @return array|null|string
	 */
	public function translate($source)
	{
		require_once(__DIR__ . '/../../../libs/jparser/jparser.php');
		require_once(__DIR__ . '/../../../libs/jparser/jtokenizer.php');

		// register custom error handler to catch STRICT exceptions thrown by JParser
		set_error_handler(array($this, 'exceptions_error_handler'));

		$tree = JParser::parse_string($source);

		restore_error_handler();

		return $this->walk($tree);
	}

	public function isValidPHP($source)
	{
		try {
			return (create_function('', $source) !== FALSE);

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * Helper method only to configure custom error handling.
	 * http://stackoverflow.com/questions/5373780/how-to-catch-this-error-notice-undefined-offset-0
	 * @param $severity
	 * @param $message
	 * @param $filename
	 * @param $lineno
	 * @throws TranslationException
	 */
	public function exceptions_error_handler($severity, $message, $filename, $lineno)
	{
		if (error_reporting() == 0) {
			return;
		}
		if (error_reporting() & $severity) {
			throw new TranslationException($message);
		}
	}

	#endregion

	#region "Protected methods"

	/**
	 * Walk the AST and transform allowed nodes.
	 * @param ParseNode $element
	 * @return array|null|string
	 * @throws TranslationException
	 */
	protected function walk(ParseNode $element)
	{
		switch ($element->scalar_symbol()) {
			case J_CALL_EXPR:
				return $this->walkJCallExprNode($element);

			case J_MEMBER_EXPR:
				return $this->walkJMemberExprNode($element);

			case J_NUMERIC_LITERAL:
			case J_STRING_LITERAL:
			case J_FOR:
			case J_IF:
			case J_ELSE:
				return $element->evaluate();

			case J_IDENTIFIER:
				return $this->walkIdentifier($element);

			case J_ADD_EXPR:
				return $this->walkAddExprNode($element);

			case J_ITER_STATEMENT:
				return $this->walkIterStatementNode($element);

			case J_FALSE:
				return 'false';
			case J_TRUE:
				return 'true';
			case J_NULL:
				return 'null';
			case J_RETURN:
				return 'return ';
			case J_CONT_STATEMENT:
				return 'continue;';
			case J_BREAK_STATEMENT:
				return 'break;';
			case J_EMPTY_STATEMENT:
				return '';

			case '=':
			case ';':
			case '(':
			case ')':
			case '-':
			case '*':
			case '/':
			case '<':
			case '>':
			case '{':
			case '}':
			case '--':
			case '++':
			case '==':
			case '!=':
			case '<=':
			case '>=':
			case '&&':
			case '||':
			case ',':
			case '!':
			case '%':
			case '[':
			case ']':
				return $element->scalar_symbol();

			case J_PROGRAM:
			case J_ELEMENTS:
			case J_STATEMENT:
			case J_EXPR_STATEMENT:
			case J_EXPR:
			case J_EXPR_NO_IN:
			case J_ARGS:
			case J_ARG_LIST:
			case J_ASSIGN_EXPR:
			case J_ASSIGN_EXPR_NO_IN:
			case J_IF_STATEMENT:
			case J_LOG_AND_EXPR:
			case J_LOG_OR_EXPR:
			case J_EQ_EXPR:
			case J_REL_EXPR:
			case J_MULT_EXPR:
			case J_BLOCK:
			case J_STATEMENT_LIST:
			case J_UNARY_EXPR:
			case J_PRIMARY_EXPR:
			case J_RETURN_STATEMENT:
			case J_POSTFIX_EXPR:
				break;

			case J_FUNC_DECL:
				throw new TranslationException('Functions are not supported.');

			default:
				throw new TranslationException('Unrecognized token: ' . j_token_name($element->scalar_symbol()));
		}

		$result = '';
		if ($element->has_children()) {
			$x = $element->reset();
			while ($x) {
				$result .= $this->walk($x);
				$x = $element->next();
			}

		} else {
			throw new TranslationException('Unrecognized sequence: ' . j_token_name($element->scalar_symbol()) . ' ' . $element->evaluate());
		}

		return $result;
	}

	/**
	 * Accessing member's property (e.g. "abc.efg")
	 * @param ParseNode $element
	 * @return string
	 * @throws TranslationException
	 */
	protected function walkJMemberExprNode(ParseNode $element)
	{
		$result = '';
		$first = true;
		/** @var ParseNode $x */
		$x = $element->reset();
		while ($x) {
			if ($first) {
				// first member in the expression (e.g. "abc" in "abc.cde")
				$nodeValue = $x->evaluate();

				if ($x->scalar_symbol() == J_NEW) {
					throw new TranslationException('New operator is not supported. To initialize array, use "variable = array()"');

				} else if (array_key_exists($nodeValue, $this->contextVariables)) {
					// custom helper variable
					$result .= $this->contextVariables[$nodeValue];

				} elseif (array_key_exists($nodeValue, $this->staticContextVariables)) {
					// maybe custom static variables

					// look ahead to check if the property is allowed
					$element->next(); // skip "->" node
					$y = $element->next();
					if (array_key_exists($y->evaluate(), $this->staticContextVariables[$nodeValue])) {
						$result .= $this->staticContextVariables[$nodeValue][$y->evaluate()];

					} else {
						// property is not allowed
						throw new TranslationException('Invalid expression ' . $nodeValue . '.' . $y->evaluate());
					}

				} else {
					// standard variable (annotated by "$" in PHP)
					$result .= $this->walk($x);
				}

				$first = false;

			} elseif ($x->evaluate() == '.') {
				// dot notation in JavaScript is transformed to "->" notation in PHP
				$result .= '->';

			} else {
				// other member in the expression (e.g. "cde" in "abc.cde")
				if ($x->has_children()) {
					$result .= $this->walk($x);
				} else {

					$result .= $x->evaluate();
				}
			}

			$x = $element->next();
		}

		return $result;
	}

	/**
	 * Calling function (e.g. "cde()")
	 * @param ParseNode $element
	 * @return string
	 * @throws TranslationException
	 */
	protected function walkJCallExprNode(ParseNode $element)
	{
		$return = '';
		/** @var ParseNode $x */
		$x = $element->reset();
		while ($x) {
			if ($x->scalar_symbol() == J_IDENTIFIER) {
				if (!in_array($x->evaluate(), $this->allowedFunctions))
					throw new TranslationException('Unknown function: ' . $x->evaluate());

				$return .= $x->evaluate();

			} else {
				$return .= $this->walk($x);
			}

			$x = $element->next();
		}

		return $return;
	}

	/**
	 * Addition expression (a + b). Based on types (string/integer)
	 * translates '+' to '+' (integer) * or '.' (concatenation, string)
	 * @param ParseNode $element
	 * @return string
	 */
	protected function walkAddExprNode(ParseNode $element)
	{
		$isString = false;
		/** @var ParseNode $x */
		$x = $element->reset();
		while ($x) {
			if ($x->scalar_symbol() == J_STRING_LITERAL)
				$isString = true; // string literal, use "." to concatenation

			$x = $element->next();
		}

		$return = '';
		$x = $element->reset();
		while ($x) {
			if ($x->scalar_symbol() == '+') {
				$return .= $isString ? '.' : '+';

			} else {
				$return .= $this->walk($x);
			}

			$x = $element->next();
		}

		return $return;
	}

	/**
	 * Iteration (for/while) loop.
	 * @param ParseNode $element
	 * @return string
	 * @throws TranslationException
	 */
	public function walkIterStatementNode(ParseNode $element)
	{
		$type = $element->reset();
		$result = '';

		if ($type->scalar_symbol() == J_FOR || $type->scalar_symbol() == J_WHILE) {
			$result .= $type->evaluate();

			while ($x = $element->next()) {
				$result .= $this->walk($x);
			}

		} else {
			throw new TranslationException('Unknown iteration type ' . $type->evaluate());
		}

		return $result;
	}

	/**
	 * Identifier. Checks blacklisted variables and transforms them adding prefix "_sys_".
	 * (e.g. "_GET" is transformed to "_sys__GET")
	 * @param ParseNode $element
	 * @return string
	 */
	public function walkIdentifier(ParseNode $element)
	{
		$variable = $element->evaluate();

		if (in_array($variable, $this->disabledVariables))
			$variable = '_sys_' . $variable;

		return '$' . $variable;
	}

	#endregion
}