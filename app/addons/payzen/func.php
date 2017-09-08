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

function fn_payzen_order_placement_routines($order_id, $force_notification, $order_info, $_error)
{
    if (key_exists('vads_hash', $_POST) && isset($_POST['vads_hash'])) {
        // server call : delete notifications and skip redirection
        fn_delete_notification('');
        exit;
    }
}
