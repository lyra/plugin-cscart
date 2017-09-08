<?php
/**
 * PayZen V2-Payment Module version 2.0.0 for CS-Cart 4.x. Support contact : support@payzen.eu.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Lyra Network (http://www.lyra-network.com/)
 * @copyright 2014-2017 Lyra Network and contributors
 * @license   https://opensource.org/licenses/mit-license.html  The MIT License (MIT)
 * @category  payment
 * @package   payzen
 */

use Tygh\Registry;

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.payzen_get.php
 * Type:     function
 * Name:     payzen_get
 * Purpose: get PayZen supported languages or card types according to 'what' parameter
 *          and assign it to a variable whose name is passed in 'to' parameter
 * Parameter:
 *    - what = what to get (langs or cards) from PayZen API (required)
 *    - to   = name of a variable to which assign result (if none return it)
 * -------------------------------------------------------------
 */
function smarty_function_payzen_get($params, $template)
{
    if (empty($params['what'])) {
        trigger_error("[plugin] payzen_get parameter 'what' cannot be empty", E_USER_NOTICE);

        return;
    }

    require_once Registry::get('config.dir.payments') . 'payzen/payzen_api.php';

    $result = array();

    if ($params['what'] == 'langs') {
        $result = PayzenApi::getSupportedLanguages();
    } elseif ($params['what'] == 'cards') {
        $result = PayzenApi::getSupportedCardTypes();
    } else {
        trigger_error("[plugin] payzen_get parameter 'what' is invalid, expected values are 'langs' and 'cards'", E_USER_NOTICE);

        return;
    }

    if (empty($params['to'])) {
        return $result;
    } else {
        $template->assign($params['to'], $result);
    }
}
