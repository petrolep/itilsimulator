<?php
/**
 * JavaScriptTranslatorTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.5.13 19:40
 */

namespace ITILSimulator\Tests;

require_once(__DIR__ . '/../../bootstrap.php');

class JavaScriptTranslatorTest extends ITIL_TestCase
{
	/** @var \ITILSimulator\Base\JavaScriptTranslator\JavaScriptTranslator  */
	protected $translator;

	protected function setUp()
	{
		$this->translator = new \ITILSimulator\Base\JavaScriptTranslator\JavaScriptTranslator();
	}

	public function testAllowed()
	{
		$t = $this->translator;

		// variable
		$this->assertEquals('$abc=10;', $t->translate('abc = 10'));
		// property access
		$this->assertEquals('$abc->cde;', $t->translate('abc.cde'));
		// method call
		$this->assertEquals('$abc->cde(123);', $t->translate('abc.cde(123)'));
		// for loop
		$this->assertEquals('for($i=0;$i<10;$i++)$abc--;', $t->translate('for(i = 0; i < 10; i++) abc--'));
		// for + object access
		$this->assertEquals('for($i=0;$i<10;$i++){$abc=array();$abc->length->ads=$i*2;}', $t->translate('for(i = 0; i < 10; i++) { abc = array(); abc.length.ads = i*2;}'));
		// while + return
		$this->assertEquals('while($abc){return true;}', $t->translate('while(abc) { return true;};'));
		// while loop
		$this->assertEquals('while($i>10){return $abc->cde;}', $t->translate('while(i > 10) { return abc.cde;}'));
		// loop break
		$this->assertEquals('while(true)break;', $t->translate('while(true) break'));
		// loop continue
		$this->assertEquals('while(true)continue;', $t->translate('while(true) continue'));
		// empty statement
		$this->assertEquals('', $t->translate(';'));
		// allowed array function, array access
		$this->assertEquals('if($abc<10){while(true){$abc=array();$abc["cde"]=$abc."aaaa";}}', $t->translate('if (abc < 10) { while(true) { abc = array(); abc["cde"] = abc + "aaaa"; } }'));
		// disabled superglobals
		$this->assertEquals('$_sys_GLOBALS="a";$_sys__SERVER=$_sys__GET+$_sys__POST+$_sys__FILES+$_sys__COOKIE+$_sys__SESSION+$_sys__REQUEST+$_sys__ENV;', $t->translate('GLOBALS="a";_SERVER=_GET+_POST+_FILES+_COOKIE+_SESSION+_REQUEST+_ENV;'));
	}

	public function testDisallowedFunctions()
	{
		$problematicCode = array(
			// function call
			'abc()',
			// assign result
			'x = efg(10, 20, 30)',
			// declare function
			'function myfunction() { return x; }'
		);

		foreach ($problematicCode as $code) {
			try {
				$this->translator->translate($code);

				throw new \Exception('Test was supposed to fail: ' . $code);

			} catch(\ITILSimulator\Base\JavaScriptTranslator\TranslationException $e) {

			}
		}
	}
}