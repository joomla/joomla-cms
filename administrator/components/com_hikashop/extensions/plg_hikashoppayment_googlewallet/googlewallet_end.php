<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_googlewallet_end" id="hikashop_googlewallet_end">

    <script src="<?php echo $this->payment_params->url; ?>"></script>

    <script type="text/javascript">

        function purchase()
        {
            var successHandler = function(result)
            {

                alert('ok');

            }

            var failureHandler = function(result)
            {

                alert('nok');

            }

            google.payments.inapp.buy(
            {

                'jwt': "<?php echo $this->token; ?>",

                'success': "<?php echo $this->payment_params->succes_url ?>",

                'failure': "<?php echo $this->payment_params->cancel_url ?>"

            });

        }

        window.hikashop.ready(purchase());

    </script>

</div>

