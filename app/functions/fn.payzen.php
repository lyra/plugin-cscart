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

function fn_payzen_convert_trans_status($payzenStatus, $successStatus = 'P')
{
    switch ($payzenStatus) {
        case 'INITIAL':
        case 'WAITING_AUTHORISATION_TO_VALIDATE':
        case 'WAITING_AUTHORISATION':
            return 'O'; // Open

        case 'AUTHORISED_TO_VALIDATE':
        case 'AUTHORISED':
        case 'CAPTURE_FAILED':
        case 'CAPTURED':
            return $successStatus; // status configured in module backend

        case 'CANCELLED':
        case 'ABANDONED':
            return 'I'; // Canceled

        case 'EXPIRED':
        case 'NOT_CREATED':
        case 'REFUSED':
        default :
            return 'F'; // Failed
    }
}
