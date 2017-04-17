<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopWidgetClass extends hikashopClass {
	var $pkeys=array('widget_id');
	var $tables=array('widget');
	var $toggle = array('widget_published'=>'widget_id');

	var $order_type = 'sale';

	function get($cid=0,$default=''){
		if(!empty($cid)){
			$widget = parent::get($cid);
			if(!empty($widget->widget_params)){
				$widget->widget_params = unserialize($widget->widget_params);
				if(!empty($widget->widget_params->status)){
					$widget->widget_params->status = explode(',',$widget->widget_params->status);
				}
			}
			return $widget;
		}
		$filters=array();
		$filters[]='widget_published=1';
		hikashop_addACLFilters($filters,'widget_access');
		$filters=implode(' AND ', $filters);
		$query = 'SELECT * FROM '.hikashop_table('widget').' WHERE '.$filters.' ORDER BY widget_ordering ASC';
		$this->database->setQuery($query);
		$widgets = $this->database->loadObjectList();
		if(!empty($widgets)){
			foreach($widgets as $k => $widget){
				if(!empty($widget->widget_params)){
					$widgets[$k]->widget_params = unserialize($widget->widget_params);
					if(!empty($widgets[$k]->widget_params->status)){
						$widgets[$k]->widget_params->status = explode(',',$widgets[$k]->widget_params->status);
					}
				}
			}
		}
		return $widgets;
	}

	function save(&$element){
		if(!empty($element->widget_params) && !is_string($element->widget_params)){
			if($element->widget_params->display=='listing' && !isset($element->widget_params->region)){
				$element->widget_params->region='world';
			}
			if(!isset($element->widget_params->status)) $element->widget_params->status='';
			if(is_array($element->widget_params->status)){
				$element->widget_params->status = implode(',',$element->widget_params->status);
			}
			$element->widget_params = serialize($element->widget_params);
		}
		return parent::save($element);
	}

	function saveForm(){
		$widget = new stdClass();
		$table = new stdClass();
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		$deleteRow = JRequest::getVar( 'delete_row');
		$widget->widget_id = hikashop_getCID('widget_id');
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
		if(!empty($formData)){
			if(isset($formData['edit_row'])){
				$widget_id=$formData['widget']['widget_id'];
				$class = hikashop_get('class.widget');
				if(!empty($widget_id)){
					$widget = $class->get($widget_id);
				}


				$widget->widget_name=$safeHtmlFilter->clean(strip_tags($formData['widget']['widget_name']), 'string');
				$widget->widget_published=(int)$formData['widget']['widget_published'];
				$widget->widget_access=$safeHtmlFilter->clean(strip_tags($formData['widget']['widget_access']), 'string');
				if(!isset($widget->widget_params)) $widget->widget_params = new stdClass();
				$widget->widget_params->display='table';

				foreach($formData['widget']['widget_params']['table'] as $key=>$tab){
					$theKey=$key;
				}


				foreach($formData['widget']['widget_params']['table'][$theKey] as $column => $value){
					hikashop_secureField($column);
					if(is_array($value)){
						$table->$column=new stdClass();
						foreach($value as $k2 => $v2){
							hikashop_secureField($k2);
							if($k2 == 'start' || $k2 == 'end'){
								 $v2 = hikashop_getTime($v2);
							}
							if(is_array($v2)){
								if($k2=='filters' || $k2=="compares"){
									$v2 = serialize($v2);
								}
								else{
									$v2 = implode(',',$v2);
								}
							}
							$table->{$column}->$k2 = $safeHtmlFilter->clean(strip_tags($v2), 'string');
						}
					}else{
						$table->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
					}
				}
				$categories = JRequest::getVar( 'row_category', array(), '', 'array' );
				JArrayHelper::toInteger($categories);
				$cat=array();
				foreach($categories as $category){
					$cat[]=$category;
				}
				if(empty($cat)){
					$cat='all';
				}else{
					$cat=implode(',',$cat);
				}
				$widget->widget_params->categories = $cat;

				$coupons = JRequest::getVar( 'row_coupon', array(), '', 'array' );
				JArrayHelper::toInteger($coupons);
				$coupons=serialize($coupons);
				$widget->widget_params->coupons = $coupons;
				$widget->widget_params->table[$theKey]=$table;
			}else if($formData['widget']['widget_params']['display']=='table'){
				$class = hikashop_get('class.widget');
				if(!empty($widget->widget_id)){
					$widget = $class->get($widget->widget_id);
				}
				if($deleteRow>=0){
					unset($widget->widget_params->table[$deleteRow]);
				}else{
					$widget->widget_name=$safeHtmlFilter->clean(strip_tags($formData['widget']['widget_name']), 'string');
					$widget->widget_published=(int)$formData['widget']['widget_published'];
					$widget->widget_access=$safeHtmlFilter->clean(strip_tags($formData['widget']['widget_access']), 'string');
				}
			}else{
				if($formData['widget']['widget_params']['periodType'] && isset($formData['widget']['widget_params']['proposedPeriod']) && $formData['widget']['widget_params']['proposedPeriod']=='all'){
					$formData['widget']['widget_params']['period_compare']='none';
				}
				foreach($formData['widget'] as $column => $value){
					hikashop_secureField($column);
					if(is_array($value)){
						$widget->$column=new stdClass();
						foreach($value as $k2 => $v2){
							hikashop_secureField($k2);
							if(is_array($v2)){
								if($k2=='filters' || $k2=="compares"){
									$v2=serialize($v2);
								}
								else{
									$v2 = implode(',',$v2);
								}
							}
							$widget->{$column}->$k2 = $safeHtmlFilter->clean(strip_tags($v2), 'string');
						}
					}else{
						$widget->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
					}
				}
			}
		}
		if(!empty($widget->widget_params->start)){
			$widget->widget_params->start = hikashop_getTime($widget->widget_params->start);
		}
		if(!empty($widget->widget_params->end)){
			$widget->widget_params->end = hikashop_getTime($widget->widget_params->end);
		}
		if(isset($widget->widget_params->compare_with)){
			if($widget->widget_params->compare_with=='periods'){
				$widget->widget_params->compares=null;
			}
		}
		$categories = JRequest::getVar( 'category', array(), '', 'array' );
		JArrayHelper::toInteger($categories);
		$cat=array();
		foreach($categories as $category){
			$cat[]=$category;
		}
		if(empty($cat)){
			$cat='all';
		}else{
			$cat=implode(',',$cat);
		}

		$products = JRequest::getVar( 'widget', array(), '', 'array' );
		JArrayHelper::toInteger($products);
		$prods=serialize($products);

		$coupons = JRequest::getVar( 'coupon', array(), '', 'array' );
		JArrayHelper::toInteger($coupons);
		$coupons=serialize($coupons);

		if(isset($formData['edit_row'])){
			$widget->widget_params->table[$theKey]->widget_params->categories = $cat;
			$widget->widget_params->table[$theKey]->widget_params->products = $prods;
			$widget->widget_params->table[$theKey]->widget_params->coupons = $coupons;
		}else{
			$widget->widget_params->categories = $cat;
			$widget->widget_params->products = $prods;
			$widget->widget_params->coupons = $coupons;
		}

		$status=$this->save($widget);

		if($status){
			$orderClass = hikashop_get('helper.order');
			$orderClass->pkey = 'widget_id';
			$orderClass->table = 'widget';
			$orderClass->orderingMap = 'widget_ordering';
			$orderClass->reOrder();
		}

		return $status;
	}

	function displayResult($results){
		?>
		<script language="JavaScript" type="text/javascript">
			function drawChart(){
				var dataTable = new google.visualization.DataTable();
				dataTable.addColumn('string');
				<?php
				$dates = array();
				$types = array();
				$i= 0;
				$a = 1;
				foreach($results as $oneResult){
					if(!isset($dates[$oneResult->groupingdate])){
						$dates[$oneResult->groupingdate] = $i;
						$i++;
						echo "dataTable.addRows(1);"."\n";
						echo "dataTable.setValue(".$dates[$oneResult->groupingdate].", 0, '".strftime($this->dateformat,strtotime($oneResult->groupingdate))."');";
					}
					if(!isset($types[$oneResult->type])){
						$types[$oneResult->type] = $a;
						echo "dataTable.addColumn('number','".$oneResult->type."');"."\n";
						$a++;
					}
					echo "dataTable.setValue(".$dates[$oneResult->groupingdate].", ".$types[$oneResult->type].", ".$oneResult->total.");";
				}
				?>

				var vis = new google.visualization.<?php echo $this->charttype; ?>(document.getElementById('chart'));
				var options = {
					width:1200,
					height:500,
					legend:'right',
					title: 'Orders',
					legendTextStyle: {color:'#333333'}
				};
				vis.draw(dataTable, options);
			}

			google.load("visualization", "1", {packages:["corechart"]});
			google.setOnLoadCallback(drawChart);
		</script>
		<?php
	}

	function csv(){
		if(hikashop_level(2)){
			$widget_id = hikashop_getCID('widget_id');
			if($widget_id){
				$widget = $this->get($widget_id);
				if($widget->widget_params->display=='table'){
					$app = JFactory::getApplication();
					$message=JText::_('CANNOT_EXPORT_THIS_FILE');
					$app->enqueueMessage( $message );
					$app->redirect(hikashop_completeLink("report&task=edit&cid[]=".$widget_id, false, true));
				}
				$this->data($widget,true);
				$encodingClass = hikashop_get('helper.encoding');
				@ob_clean();
				header("Pragma: public");
				header("Expires: 0"); // set expiration time
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/force-download");
				header("Content-Type: application/octet-stream");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment; filename=hikashopexport.csv");
				header("Content-Transfer-Encoding: binary");
				$eol= "\r\n";
				$config =& hikashop_config();
				$separator = $config->get('csv_separator',";");
				echo implode($separator,$widget->exportFields).$eol;
				$missing = array();
				$convert_date = $config->get('convert_date',DATE_RFC822);
				foreach($widget->elements as $el){
					$line = array();
					foreach($widget->exportFields as $field){
						if(!isset($missing[$field])){
							if(isset($el->$field)){
								if($convert_date && in_array($field,array('user_created','order_created','order_modified'))) $el->$field=hikashop_getDate($el->$field,$convert_date);
								if($field == 'calculated_date')	$el->$field=hikashop_getDate($el->timestamp,'d-M-Y');
								$line[]='"'.str_replace(array("\r","\n"),array('\r','\n'),$el->$field).'"';
							}else{
								$missing[$field]=$field;
							}
						}
					}
					if(empty($missing)){
						echo $encodingClass->change(implode($separator,$line),'UTF-8',$widget->widget_params->format).$eol;
					}
				}
				if(!empty($missing)){
					@ob_clean();
					$fieldsLeft = array();
					foreach($widget->exportFields as $field){
						if(!isset($missing[$field])){
							$fieldsLeft[]=$field;
						}
					}
					echo implode($separator,$fieldsLeft).$eol;

					foreach($widget->elements as $el){
						$line = array();
						foreach($fieldsLeft as $field){
							if($convert_date && in_array($field,array('user_created','order_created','order_modified'))) $el->$field=hikashop_getDate($el->$field,$convert_date);
							$line[]='"'.str_replace(array("\r","\n"),array('\r','\n'),$el->$field).'"';
						}
						echo $encodingClass->change(implode($separator,$line),'UTF-8',$widget->widget_params->format).$eol;
					}
				}
				exit;
			}else{
				$app = JFactory::getApplication();
				$app->enqueueMessage();
				$this->listing();
			}
		}
	}

	function loadDatas(&$element){
		$db = JFactory::getDBO();
		if(isset($element->widget_params->filters) && !empty($element->widget_params->filters) && is_string($element->widget_params->filters)){
			$element->widget_params->filters=unserialize($element->widget_params->filters);
		}
		if(isset($element->widget_params->compares) && !empty($element->widget_params->compares) && $element->widget_params->compares!='0:,' && is_string($element->widget_params->compares)){
			$element->widget_params->compares=unserialize($element->widget_params->compares);
		}

		if(isset($element->widget_params->categories) && $element->widget_params->categories!='all' && is_string($element->widget_params->categories)){
			$element->widget_params->categories_list = $element->widget_params->categories;
			$categories=array();
			$this->categories= explode(",", $element->widget_params->categories);
			if(!empty($this->categories)){
				foreach($this->categories as $k => $cat){
					$categories[$k] = new stdClass();
					$categories[$k]->category_id=$cat;
				}
				$db->setQuery('SELECT * FROM '.hikashop_table('category').' WHERE category_id IN ('.implode(',',$this->categories).')');
				$cats = $db->loadObjectList('category_id');
				foreach($this->categories as $k => $cat){
					$element->widget_params->filters['cat.category_id'][$cat]=$cat;
					if(!empty($cats[$cat])){
						$categories[$k]->category_name = $cats[$cat]->category_name;
					}else{
						$categories[$k]->category_name = JText::_('CATEGORY_NOT_FOUND');
					}
				}
			}
			$element->widget_params->categories=$categories;
			if($element->widget_params->category_childs && !empty($categories)){
				$parents=array();
				foreach($categories as $cat){
					$parents[]=$cat->category_id;
				}
				$categoryClass = hikashop_get('class.category');
				$childs = $categoryClass->getChildren($parents,true);
				$childs_id=array();
				foreach($childs as $child){
					$childs_id[]=$child->category_id;
				}
				$element->widget_params->childs=$childs_id;
			}
		}

		if(isset($element->widget_params->products) && is_string($element->widget_params->products)){
			$element->widget_params->products_list = $element->widget_params->products;
			$products=array();
			$this->products= unserialize($element->widget_params->products);
			if(!empty($this->products)){
				foreach($this->products as $k => $prod){
					$products[$k] = new stdClass();
					$products[$k]->product_id=$prod;
				}

				$db->setQuery('SELECT * FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$this->products).')');
				$prods = $db->loadObjectList('product_id');
				foreach($this->products as $k => $prod){
					$element->widget_params->filters['prod.product_id'][$prod]=$prod;
					if(!empty($prods[$prod])){
						$products[$k]->product_name = $prods[$prod]->product_name;
					}else{
						$products[$k]->product_name = JText::_('PRODUCT_NOT_FOUND');
					}
				}
			}
			$element->widget_params->products=$products;
		}

		if(isset($element->widget_params->payment) && !empty($element->widget_params->payment)){
			if(!is_array($element->widget_params->payment)){
				$element->widget_params->payment = explode(',',$element->widget_params->payment);
				$methods=array();
				foreach($element->widget_params->payment as $paymentMethod){
					$temp=explode('_',$paymentMethod);
					$methods[]=$temp[0];
				}
				$element->widget_params->filters['a.order_payment_method']=$methods;
			}
		}

		if(isset($element->widget_params->shipping) && !empty($element->widget_params->shipping)){
			if(!is_array($element->widget_params->shipping)){
				$element->widget_params->shipping=explode(',',$element->widget_params->shipping);
				$methods=array();
				foreach($element->widget_params->shipping as $paymentMethod){
					$temp=explode('_',$paymentMethod);
					$methods[]=$temp[0];
					$ids[]=$temp[1];
				}
				$element->widget_params->filters['a.order_shipping_method']=$methods;
				$element->widget_params->filters['a.order_shipping_id']=$ids;
			}
		}

		if(isset($element->widget_params->coupons) && !empty($element->widget_params->coupons)){
			$element->widget_params->coupons_list = $element->widget_params->coupons;
			if(is_string($element->widget_params->coupons))$element->widget_params->coupons=unserialize($element->widget_params->coupons);
			if(!empty($element->widget_params->coupons)){
				foreach($element->widget_params->coupons as $k => $coupon){
					if(is_object($coupon)){
						$element->widget_params->coupons[$k] = $coupon->discount_id;
					}
				}
				$db->setQuery('SELECT * FROM '.hikashop_table('discount').' WHERE discount_id IN ('.implode(',',$element->widget_params->coupons).')');
				$couponList = $db->loadObjectList();
				foreach($couponList as $coupon){
					$coupons[]=$coupon->discount_code;
				}
				$element->widget_params->coupons = $db->loadObjectList();
				$element->widget_params->filters['a.order_discount_code']=$coupons;
			}
		}

		if(!empty($element->widget_params->shipping)){
			$element->widget_params->shipping_id = array();
			$element->widget_params->shipping_type = array();
			foreach($element->widget_params->shipping as $method){
				list($shipping_type,$shipping_id) = explode('_',$method,2);
				$element->widget_params->shipping_id[] = $shipping_id;
				$element->widget_params->shipping_type[] = $shipping_type;
			}
		}else{
			$element->widget_params->shipping_id = array();
			$element->widget_params->shipping_type = array();
		}

		if(!empty($element->widget_params->payment)){
			$element->widget_params->payment_id = array();
			$element->widget_params->payment_type = array();
			foreach($element->widget_params->payment as $method){
				list($shipping_type,$shipping_id) = explode('_',$method,2);
				$element->widget_params->payment_id[] = $shipping_id;
				$element->widget_params->payment_type[] = $shipping_type;
			}
		}else{
			$element->widget_params->payment_id = array();
			$element->widget_params->payment_type = array();
		}
	}

	function data(&$widget,$csv=false){
		$this->loadDatas($widget);

		$filters = array();
		$leftjoin = array();
		$groupby_add='';
		$select='SELECT ';
		$pageInfo=null;
		$type = array();
		$fieldtype='';
		$date_field='';
		$diff=0;
		$selectAdd='';
		$db = JFactory::getDBO();
		if(!hikashop_level(2)){
			if($widget->widget_params->content=='partners' || $widget->widget_params->display=='map') return false;
			if(!hikashop_level(1) && in_array($widget->widget_params->display,array('gauge','pie'))) return false;
		}

		if(isset($widget->widget_params->periodType) && $widget->widget_params->periodType=='proposedPeriod'){
			$widget->widget_params->end=time();
			switch($widget->widget_params->proposedPeriod){
				case 'all':
					$widget->widget_params->period=0;
					$widget->widget_params->start='';
					$widget->widget_params->end='';
					break;
				case 'today': //TO CHECK!!
					$dayBeginning = hikashop_getDate(time(),'%m,%d,%Y');
					$dayBeginning=explode(',',$dayBeginning);
					$start= mktime(0, 0, 0, $dayBeginning[0], $dayBeginning[1], $dayBeginning[2]);
					$widget->widget_params->start=$start;
					break;
				case 'yesterday':
					$yesterdayDate=$widget->widget_params->end-86400;
					$yesterdayDate=hikashop_getDate($yesterdayDate,'%m,%d,%Y');
					$yesterdayDate=explode(',',$yesterdayDate);
					$start= mktime(0, 0, 1, $yesterdayDate[0], $yesterdayDate[1], $yesterdayDate[2]);
					$end= mktime(23, 59, 59, $yesterdayDate[0], $yesterdayDate[1], $yesterdayDate[2]);
					echo hikashop_getDate($start,'%m,%d,%Y');
					$widget->widget_params->start=$start;
					$widget->widget_params->end=$end;
					break;
				case 'last24h':
					$widget->widget_params->start=$widget->widget_params->end-86400;
					break;
				case 'last7d':
					$widget->widget_params->start=$widget->widget_params->end-604800;
					break;
				case 'thisWeek':
					$widget->widget_params->start=strtotime('this week', time());
					break;
				case 'last30d':
					$widget->widget_params->start=$widget->widget_params->end-2592000;
					break;
				case 'thisMonth':
					$widget->widget_params->start=mktime(0, 0, 0, date("n"), 1);
					break;
				case 'last365d':
					$widget->widget_params->start=$widget->widget_params->end-31536000;
					break;
				case 'thisYear':
					$widget->widget_params->start = strtotime('1 january '.date("Y"));
					break;
				case 'previousWeek':
					$currentDate=hikashop_getDate($widget->widget_params->end,'%m,%w,%d,%Y');
					$previousWeekStart=explode(',',$currentDate);
					$previousWeekEnd=explode(',',$currentDate);
					$previousWeekStart[2]=$previousWeekStart[2]-6-$previousWeekStart[1];
					$previousWeekEnd[2]=$previousWeekEnd[2]-7+(7-$previousWeekEnd[1]);
					$start= mktime(0, 0, 0, $previousWeekStart[0], $previousWeekStart[2], $previousWeekStart[3]);
					$end= mktime(23, 59, 59, $previousWeekEnd[0], $previousWeekEnd[2], $previousWeekEnd[3]);
					$widget->widget_params->start=$start;
					$widget->widget_params->end=$end;
					break;
				case 'previousMonth':
					$currentDate=hikashop_getDate($widget->widget_params->end,'%m,%d,%Y');
					$previousMonthStart=explode(',',$currentDate);
					$previousMonthEnd=explode(',',$currentDate);

					$previousMonthStart[0]=$previousMonthStart[0]-1;

					$previousMonthStart[1]=1; //First day of previous month
					$previousMonthEnd[1]=1; //First day of current month
					$start= mktime(0, 0, 0, $previousMonthStart[0], $previousMonthStart[1], $previousMonthStart[2]);
					$end= mktime(0, 0, 0, $previousMonthEnd[0], $previousMonthEnd[1], $previousMonthEnd[2])-1;
					$widget->widget_params->start=$start;
					$widget->widget_params->end=$end;
					break;
				case 'previousYear':
					$currentDate=hikashop_getDate($widget->widget_params->end,'%m,%d,%Y');
					$previousMonthStart=explode(',',$currentDate);
					$previousMonthEnd=explode(',',$currentDate);

					$previousMonthStart[2]=$previousMonthStart[2]-1;

					$previousMonthStart[1]=1; //First day of previous year
					$previousMonthEnd[1]=1; //First day of current year
					$previousMonthStart[0]=1; //First month of previous year
					$previousMonthEnd[0]=1; //First month of current year
					$start= mktime(0, 0, 0, $previousMonthStart[0], $previousMonthStart[1], $previousMonthStart[2]);
					$end= mktime(0, 0, 0, $previousMonthEnd[0], $previousMonthEnd[1], $previousMonthEnd[2])-1;
					$widget->widget_params->start=$start;
					$widget->widget_params->end=$end;
					break;
			}
		}

		if(isset($widget->widget_params->period_compare) && $widget->widget_params->period_compare!='null' && isset($widget->elements)){
			switch($widget->widget_params->period_compare){
				case 'last_period':
					$diff=$widget->widget_params->end-$widget->widget_params->start;
					$widget->widget_params->end=$widget->widget_params->start;
					$widget->widget_params->start=$widget->widget_params->end-$diff;
					break;
				case 'last_year':
					$widget->widget_params->end=strtotime("-1 year", $widget->widget_params->end);
					$widget->widget_params->start=strtotime("-1 year", $widget->widget_params->start);
					break;
				case 'average':
					$widget->widget_params->end='';
					$widget->widget_params->start='';
					break;
			}
		}

		if($widget->widget_params->display=='table' &&	empty($widget->widget_params->date_type)){
			$widget->widget_params->date_type='created';
		}

		switch($widget->widget_params->content){
			case 'orders':
			case 'sales':
			case 'taxes':
				$date_field = 'a.order_'.@$widget->widget_params->date_type;
				break;
			case 'partners':
			case 'customers':
				$date_field = 'a.user_created';
				break;
		}

		$compare=true;
		$setFilters=true;
		if(isset($widget->widget_params->compare_with)){if($widget->widget_params->compare_with=='periods') $compare=false;}
		if($widget->widget_params->display=='pie'){ $compare=false; }
		if($widget->widget_params->display=='table' && ($widget->widget_params->content=='customers' || $widget->widget_params->content=='partners')){ $compare=false; }
		if($widget->widget_params->display=='map'){
			if($widget->widget_params->content=='customers' || $widget->widget_params->content=='partners'){
				$setFilters=false;
			}
			$compare=false;
		}
		if($setFilters){
			if(isset($widget->widget_params->filters['cat.category_id'])){
				if(($widget->widget_params->content=='customers' || $widget->widget_params->content=='partners')){
					$leftjoin['order'] = ' LEFT JOIN '.hikashop_table('order').' AS o ON o.order_user_id = a.user_id ';
					$leftjoin['order_product'] = ' LEFT JOIN '.hikashop_table('order_product').' AS prod ON prod.order_id = o.order_id ';
				}else{
					$leftjoin['order_product'] = ' LEFT JOIN '.hikashop_table('order_product').' AS prod ON prod.order_id = a.order_id ';
				}
				$leftjoin['product'] = ' LEFT JOIN '.hikashop_table('product').' AS p ON prod.product_id = p.product_id ';
				$leftjoin['product_category'] = ' LEFT JOIN '.hikashop_table('product_category').' AS cat ON cat.product_id = p.product_id OR cat.product_id=p.product_parent_id';
				if($widget->widget_params->category_childs){
					$leftjoin['category'] = ' LEFT JOIN '.hikashop_table('category').' AS categ ON cat.category_id = categ.category_id ';
					$widget->widget_params->filters['cat.category_id']=array_merge($widget->widget_params->filters['cat.category_id'], $widget->widget_params->childs);
				}
			}
			if(isset($widget->widget_params->filters['prod.product_id'])){
				if(($widget->widget_params->content=='customers' || $widget->widget_params->content=='partners')){
					$leftjoin['order'] = ' LEFT JOIN '.hikashop_table('order').' AS o ON o.order_user_id = a.user_id ';
					$leftjoin['order_product'] = ' LEFT JOIN '.hikashop_table('order_product').' AS prod ON prod.order_id = o.order_id ';
				}else{
					$leftjoin['order_product'] = ' LEFT JOIN '.hikashop_table('order_product').' AS prod ON prod.order_id = a.order_id ';
				}
			}
		}

		if($compare==true){
			$limit=''; $getLimit='';
			if(isset($widget->widget_params->limit)) $getLimit=$widget->widget_params->limit;
			$filters=$this->_dateLimit($widget, $filters, $date_field);
			$filters = (empty($filters)? ' ':' WHERE ').implode(' AND ',$filters);
			if(!empty($getLimit)	&& !$csv){
				$limit=' LIMIT '.$getLimit;
			}
			if(isset($widget->widget_params->compares)){
				$leftjoin['currency'] = ' LEFT JOIN '.hikashop_table('currency').' AS d ON d.currency_id = a.order_currency_id ';
			}

			if(isset($widget->widget_params->compares['prod.order_product_name'])){
				if($widget->widget_params->content=='orders'){
					$selectAdd='prod.order_product_quantity';
				}else if($widget->widget_params->content=='sales'){
					$selectAdd='(prod.order_product_price+prod.order_product_tax)*prod.order_product_quantity';
				}
				if(!isset($leftjoin['order_product'])){
					$leftjoin['order_product'] = ' LEFT JOIN '.hikashop_table('order_product').' AS prod ON prod.order_id = a.order_id ';
				}
				if(!isset($leftjoin['product'])){
					$leftjoin['product'] = ' LEFT JOIN '.hikashop_table('product').' AS p ON prod.product_id = p.product_id ';
				}
				$ids=$this->_getBestProducts($filters, $widget->widget_params->content, $limit);
				if(!empty($ids)){
					foreach($ids as $id){
						$productIds[]=$id->order_product_name;
					}
					$widget->widget_params->filters['prod.order_product_name']=$productIds;
				}
			}
			if(isset($widget->widget_params->compares['c.category_id'])){
				if(!isset($leftjoin['order_product'])){ $leftjoin['order_product'] = ' LEFT JOIN '.hikashop_table('order_product').' AS prod ON prod.order_id = a.order_id '; }
				if(!isset($leftjoin['product_category'])){ $leftjoin['product_category'] = ' LEFT JOIN '.hikashop_table('product_category').' AS cat ON cat.product_id = prod.product_id '; }
				$leftjoin['category'] = ' LEFT JOIN '.hikashop_table('category').' AS c ON c.category_id = cat.category_id ';

				$ids = $this->_getBestCategories($filters, $widget->widget_params->content, $limit);
				if(!empty($ids)){
					foreach($ids as $id){
						$categoryIds[]=$id->category_id;
					}
					if(isset($categoryIds)){
						$widget->widget_params->filters['cat.category_id']=$categoryIds;
					}
				}
			}
			if(isset($widget->widget_params->compares['a.order_currency_id'])){
				$currencies=$this->_getBestCurrencies($filters, $limit);
				if(!empty($currencies)){
					foreach($currencies as $currency){
						$currenciesIds[]=$currency->currency_id;
					}
					$widget->widget_params->filters['a.order_currency_id']=$currenciesIds;
				}
			}
			if(isset($widget->widget_params->compares['a.order_discount_code'])){
				$filters=array();
				$filters=$this->_dateLimit($widget, $filters, $date_field);
				$filters[]='order_discount_code IS NOT NULL AND order_discount_code <> \'\'';
				$filters = (empty($filters)? ' ':' WHERE ').implode(' AND ',$filters);
				$discountCodes=$this->_getBestDiscount($filters, $limit);
				if(!empty($discountCodes)){
					foreach($discountCodes as $discountCode){
						$discountIds[]=$discountCode->order_discount_code;
					}
					$widget->widget_params->filters['a.order_discount_code']=$discountIds;
				}
			}
			if(isset($widget->widget_params->compares['a.order_shipping_method'])){
				$shippingMethods=$this->_getBestShipping($filters, $limit);
				if(!empty($shippingMethods)){
					foreach($shippingMethods as $shippingMethod){
						$shippingNames[]=$shippingMethod->order_shipping_method;
					}
					$widget->widget_params->filters['a.order_shipping_method']=$shippingNames;
				}
			}
			if(isset($widget->widget_params->compares['a.order_payment_method'])){
				$shippingMethods=$this->_getBestPayment($filters, $limit);
				if(!empty($shippingMethods)){
					foreach($shippingMethods as $shippingMethod){
						$shippingNames[]=$shippingMethod->order_payment_method;
					}
					$widget->widget_params->filters['a.order_payment_method']=$shippingNames;
				}
			}
		}
		$filters=array();
		$limit='';

		switch($widget->widget_params->content){
			case 'orders':
			case 'products':
			case 'categories':
			case 'discounts':
			case 'orders':
			case 'sales':
			case 'taxes':
				$date_field = 'a.order_'.@$widget->widget_params->date_type;
				$filters['type']='a.order_type=\''.$this->order_type.'\'';
				if(!empty($widget->widget_params->status)){
					$filters['status']='a.order_status IN (\''.implode('\',\'',$widget->widget_params->status).'\')';
				}
				if($widget->widget_params->display=='listing'){
					if($widget->widget_params->content=='products' || $widget->widget_params->content=='categories' || $widget->widget_params->content=='discounts'){
						$select.='*,';
					}else{
						$leftjoin[] = ' LEFT JOIN '.hikashop_table('user').' AS b ON a.order_user_id=b.user_id ';
						$select.='b.*,';
					}
				}
				if($widget->widget_params->content=='orders'){
					if(!empty($selectAdd)){
						if(!isset($leftjoin['order_product'])){
							$leftjoin['order_product'] = ' LEFT JOIN '.hikashop_table('order_product').' AS prod ON a.order_id=prod.order_id';
						}
						$pie= 'SUM('.$selectAdd.') AS total';
					}else{
						$pie = 'COUNT(a.order_id) AS total';
					}
				}elseif($widget->widget_params->content=='taxes'){
					if(!isset($leftjoin['order_product'])){
						$leftjoin['order_product'] = ' LEFT JOIN '.hikashop_table('order_product').' AS prod ON a.order_id=prod.order_id AND prod.order_product_tax > 0 ';
					}
					$pie = 'SUM(prod.order_product_tax*prod.order_product_quantity) AS total,a.order_currency_id AS currency_id';
					$groupby_add=', currency_id';
				}else{
					if(!empty($selectAdd)){
						$pie= 'SUM('.$selectAdd.') AS total';
					}else if(isset($widget->widget_params->filters['prod.product_id'])){
						$pie = 'SUM((prod.order_product_price+order_product_tax)*order_product_quantity) AS total,a.order_currency_id AS currency_id';
					}else{
						$pie = 'SUM(a.order_full_price) AS total,a.order_currency_id AS currency_id';
					}
					$groupby_add=', currency_id';
				}
				$widget->widget_params->content_view = 'order';
				$sum = $pie;
				$pie .=',a.order_status AS name';
				$table = 'order';
				$id = 'order_id';

				if(isset($widget->widget_params->filters['a.order_status']) && empty($widget->widget_params->filters['a.order_status'][0])){
					unset($widget->widget_params->filters['a.order_status']);
				}
				if(isset($widget->widget_params->filters['a.order_payment_method']) && empty($widget->widget_params->filters['a.order_payment_method'][0])){
					unset($widget->widget_params->filters['a.order_payment_method']);
				}
				if(isset($widget->widget_params->filters['a.order_shipping_method']) && empty($widget->widget_params->filters['a.order_shipping_method'][0])){
					unset($widget->widget_params->filters['a.order_shipping_method']);
				}

				if(isset($widget->widget_params->filters) && !empty($widget->widget_params->filters)){
					foreach($widget->widget_params->filters as $columnName => $values){
						if(!is_array($values)){
							$values = array($values);
						}
						foreach($values as $k => $val){
							$values[$k]=$db->Quote($val);
						}
						if($columnName=='prod.product_id'){
							$query='SELECT product_id FROM '.hikashop_table('product').' WHERE product_parent_id IN ('.implode(',',$values).')';
							$db->setQuery($query);
							if(!HIKASHOP_J25){
								$variants = $db->loadResultArray();
							} else {
								$variants = $db->loadColumn();
							}
							if(count($variants)) $values = array_merge($values,$variants);
						}

						$filters[] = $columnName." IN (".implode(",",$values).")";
					}
				}

				if(isset($widget->widget_params->compares) && !empty($widget->widget_params->compares) && $widget->widget_params->compares!='0:,' && $compare==true){
					foreach($widget->widget_params->compares as $columnName => $values){
						$groupby[] = $columnName;
						$type[] = $values;
					}
					$fieldtype=', '.
					$fieldtype .= empty($type) ? "'Total'" : "CONCAT('',".implode(", ' - ' ,",$type).")";
					$fieldtype.=' as type';
				}

				break;
			case 'partners':
			case 'customers':
				$widget->filter_partner = 1;
				if($widget->widget_params->content=='customers'){
					$widget->filter_partner = 0;
					$filters[]='a.user_partner_activated=0';
				}else{
					$filters[]='a.user_partner_activated=1';
				}
				if($widget->widget_params->display=='listing'){
					$leftjoin[] = ' LEFT JOIN '.hikashop_table('users',false).' AS b ON a.user_cms_id=b.id ';
					$select.='b.*,';
				}

				if(isset($widget->widget_params->filters['a.order_status']) && empty($widget->widget_params->filters['a.order_status'][0])){
					unset($widget->widget_params->filters['a.order_status']);
				}
				if(isset($widget->widget_params->filters['a.order_payment_method']) && empty($widget->widget_params->filters['a.order_payment_method'][0])){
					unset($widget->widget_params->filters['a.order_payment_method']);
				}
				if(isset($widget->widget_params->filters['a.order_shipping_method']) && empty($widget->widget_params->filters['a.order_shipping_method'][0])){
					unset($widget->widget_params->filters['a.order_shipping_method']);
				}

				if(isset($widget->widget_params->filters) && !empty($widget->widget_params->filters)){
					foreach($widget->widget_params->filters as $columnName => $values){
						$leftjoin['order'] = ' LEFT JOIN '.hikashop_table('order').' AS o ON a.user_id=o.order_user_id ';
						$columnName = str_replace('a.','o.',$columnName);
						if(is_array($values)){
							foreach($values as $k => $val){
								$values[$k]=$db->Quote($val);
							}
							$filters[] = $columnName." IN (".implode(",",$values).")";
						}else{
							$filters[] = $columnName.' = '.$db->Quote($values);
						}
					}
					$groupby[] = 'o.order_user_id';
				}

				$table = 'user';

				$date_field = 'a.user_created';
				$sum = 'COUNT(a.user_id) AS total';
				$widget->widget_params->content_view = 'user';
				$id = 'user_id';
				break;
		}


		switch($widget->widget_params->display){
			case 'gauge':
			case 'column':
			case 'area':
			case 'line':
			case 'graph':
				$config = JFactory::getConfig();
				if(!HIKASHOP_J30){
					$timeoffset = $config->getValue('config.offset');
				} else {
					$timeoffset = $config->get('offset');
				}
				$group_string = '';
				switch($widget->widget_params->date_group){
					case '%H %j %Y':
						$group_string = '%Y %j %H';
						break;
					case '%j %Y':
						$group_string = '%Y %j';
						break;
					case '%u %Y':
						$group_string = '%Y %u';
						break;
					case '%m %Y':
						$group_string = '%Y %m';
						break;
					default:
						$group_string = $widget->widget_params->date_group;
						break;
				}
				$timeoffset = (int)($timeoffset*60*60)-(int)@$widget->widget_params->offset;
				if($timeoffset>=0){
					$timeoffset = '+'.$timeoffset;
				}
				$group = 'DATE_FORMAT(FROM_UNIXTIME(CAST('.$date_field.' AS SIGNED )'.$timeoffset.'),\''.$group_string.'\')';
				$select .=$group.' AS calculated_date, '.$sum;
				$limit.=' GROUP BY calculated_date'.$groupby_add;
				$limit.=' ORDER BY calculated_date DESC';
				break;
			case 'listing':
				$best_customers=false;
				if(isset($widget->widget_params->customers)){ if($widget->widget_params->customers=='best_customers'){ $best_customers=true; } }
				if(($widget->widget_params->content=='customers' && $best_customers) || ($widget->widget_params->content=='partners' && $widget->widget_params->partners=='best_customers')){
					$filters=$this->_dateLimit($widget, $filters, $date_field);
					$getLimit=$widget->widget_params->limit;
					if(!empty($getLimit) && !$csv){	$limit=' LIMIT '.$getLimit;	}
					$elements=$this->_getBestCustomers($filters, $widget, $limit);
					$widget->elements=$elements;
					$first = reset($elements);
					if(empty($first)){return false;}
					unset($first->user_params);
					$widget->exportFields = array_keys(get_object_vars($first));
					return true;
				}
				if($widget->widget_params->content=='products' || $widget->widget_params->content=='categories' || $widget->widget_params->content=='discounts'){
					$widget->widget_params->content_view='product';
					$getLimit=$widget->widget_params->limit;
					if(!empty($getLimit)	&& !$csv){ $limit=' LIMIT '.$getLimit;	}
					if($widget->widget_params->content=='products' || $widget->widget_params->content=='categories'){
						$filters=$this->_dateLimit($widget, $filters, $date_field);
					}else{
						$filters[]='order_discount_code IS NOT NULL AND order_discount_code <> \'\'';
					}
					if(!empty($filters)){
						$filters = (empty($filters)? ' ':' WHERE ').implode(' AND ',$filters);
					}
					$leftjoin = implode(' ',$leftjoin);

					if($widget->widget_params->product_order_by=='best') {	$reverse=false;	}
					else{ $reverse=true;	}
					if($widget->widget_params->content=='categories'){
						$ids=$this->_getBestCategories($filters, $widget->widget_params->product_data, $limit, $reverse);
						foreach($ids as $key => $id){
							if(empty($id->category_name)){
								$id->category_name=Jtext::_('CATEGORY_NOT_FOUND');
							}
						}
						$widget->elements=$ids;
						if($widget->widget_params->product_data=='sales'){
							$widget->exportFields=array('category_name', 'Total');
						}else{
							$widget->exportFields=array('category_name', 'quantity');
						}
						return true;
					}
					if($widget->widget_params->content=='products'){
						$ids=$this->_getBestProducts($filters, $widget->widget_params->product_data, $limit, $reverse, false, $leftjoin);
						$widget->elements=$ids;
						if($widget->widget_params->product_data=='sales'){
							$widget->exportFields=array('order_product_name', 'Total');
						}else{
							$widget->exportFields=array('order_product_name', 'quantity');
						}
						return true;
					}
					if($widget->widget_params->content=='discounts'){
						$widget->widget_params->content_view='discount';
						$ids=$this->_getBestDiscount($filters, $limit, $reverse, $leftjoin);
						$widget->elements=$ids;
						$widget->exportFields=array('order_discount_code', 'Total');
						return true;
					}
				}
				if($widget->widget_params->content=='customers' || $widget->widget_params->content=='partners' || $widget->widget_params->content=='orders'){
					if(!empty($id)){
						$order_last=false;
						if(isset($widget->widget_params->orders_order_by)){
							if($widget->widget_params->orders_order_by=='last'){
								$order_last=true;
							}
						}
						if($widget->widget_params->content=='customers'|| $widget->widget_params->content=='partners'){
							$limit.=' ORDER BY a.'.$id.' DESC';
						}else if($order_last){
							$limit.=' ORDER BY a.order_created DESC';
						}else{
							$limit.=' ORDER BY a.order_full_price DESC';
						}
					}
					if(!empty($widget->widget_params->limit) && !$csv){
						$limit.=' LIMIT '.(int)$widget->widget_params->limit;
					}

					$select.='a.*';
				}
				if($widget->widget_params->content == 'orders') {
					$limit = ' GROUP BY a.order_id' . $limit;
				}
				if($csv && ($widget->widget_params->content=='orders' || $widget->widget_params->content=='sales')){
					$leftjoin[]=' LEFT JOIN #__hikashop_address AS address1 ON a.order_billing_address_id=address1.address_id';
					$select.=',address1.*';
				}
				break;
			case 'pie':
				$select.=$pie;
				$limit.=' GROUP BY a.order_status'.$groupby_add;
				break;
			case 'map':
				if($widget->widget_params->content=='orders' || $widget->widget_params->content=='sales' || $widget->widget_params->content=='taxes'){
					if(!empty($widget->widget_params->map_source) && $widget->widget_params->map_source=='shipping'){
						$address='order_shipping_address_id';
					}else{
						$address='order_billing_address_id';
					}
					$leftjoin[] = ' LEFT JOIN '.hikashop_table('address').' AS b ON a.'.$address.'=b.address_id LEFT JOIN '.hikashop_table('zone').' AS z ON b.address_country=z.zone_namekey';
					$select.='*, '.$sum;
					$limit.=' GROUP BY z.zone_namekey'.$groupby_add;
				}else{
					$select.='*, '.$sum;
					$leftjoin[]= ' LEFT JOIN '.hikashop_table('address').' AS b ON a.user_id=b.address_user_id LEFT JOIN '.hikashop_table('zone').' AS z ON b.address_country=z.zone_namekey ';
					$limit.=' GROUP BY z.zone_namekey'.$groupby_add.' ORDER BY address_default';
				}
				break;
			case 'table':
				if($widget->widget_params->content=='sales' || $widget->widget_params->content=='orders' || $widget->widget_params->content=='taxes'){
					if($widget->widget_params->content=='sales'){
						if(@$widget->widget_params->with_tax && !@$widget->widget_params->include_shipping){
							$sum = 'a.order_full_price-a.order_shipping_price';
						}else if(@$widget->widget_params->with_tax && @$widget->widget_params->include_shipping){
							$sum = 'a.order_full_price';
						}else if(!@$widget->widget_params->with_tax && @$widget->widget_params->include_shipping){
							if(!isset($leftjoin['order_product'])){
								$leftjoin['order_product'] = ' LEFT JOIN '.hikashop_table('order_product').' AS prod ON a.order_id=prod.order_id ';
							}
							$sum = 'a.order_full_price+a.order_discount_tax-a.order_shipping_tax-a.order_payment_tax-(SELECT SUM(subprod.order_product_tax*subprod.order_product_quantity) FROM '.hikashop_table('order_product').' AS subprod WHERE a.order_id=subprod.order_id)';
						}else{
							if(!isset($leftjoin['order_product'])){
								$leftjoin['order_product'] = ' LEFT JOIN '.hikashop_table('order_product').' AS prod ON a.order_id=prod.order_id ';
							}
							$sum = 'prod.order_product_price*prod.order_product_quantity';
						}
						$select .= 'SUM('.$sum.') as Total, a.order_currency_id';
					}else if($widget->widget_params->content=='orders'){
						if(isset($widget->widget_params->filters['prod.product_id'])){
							if(!isset($leftjoin['order_product'])){
								$leftjoin['order_product'] = ' LEFT JOIN '.hikashop_table('order_product').' AS prod ON a.order_id=prod.order_id ';
							}
							$select .='SUM(prod.order_product_quantity) AS Total, a.order_currency_id';
						}else{
							$select.='COUNT(DISTINCT(a.order_id)) as Total, a.order_currency_id';
						}
					}else if($widget->widget_params->content=='taxes'){
						if(!isset($leftjoin['order_product'])){
							$leftjoin['order_product'] = ' LEFT JOIN '.hikashop_table('order_product').' AS prod ON a.order_id=prod.order_id ';
						}
						$select.='SUM(prod.order_product_tax*prod.order_product_quantity) as Total, a.order_currency_id';
					}
				}else if($widget->widget_params->content=='customers' || $widget->widget_params->content=='partners'){
					if($widget->widget_params->customers=='last_customer'){
						$where='';
						if($widget->widget_params->content=='partners'){
							$where=' WHERE user_partner_activated=1 ';
						}
						$query='SELECT u.name FROM '.hikashop_table('order').' AS o LEFT JOIN '.hikashop_table('user').' as hu ON o.order_user_id=hu.user_id LEFT JOIN '.hikashop_table('users',false).' as u ON u.id=hu.user_cms_id ' .
								$where.'ORDER BY o.order_created ASC LIMIT 1';
						$db->setQuery($query);
						$elements = $db->loadResult();
						$widget->elements =& $elements;
						return true;
					}
					if($widget->widget_params->customers=='total_customers'){
						$filters='';
						if($widget->widget_params->content=='partners'){
							$filters[]=' user_partner_activated=1 ';
						}
						$filters=$this->_dateLimit($widget, $filters, $date_field);
						if(!empty($filters)){
							$filters = (empty($filters)? ' ':' WHERE ').implode(' AND ',$filters);
						} else {
							$filters = '';
						}
						$query='SELECT COUNT(name) FROM '.hikashop_table('users',false).' as u LEFT JOIN '.hikashop_table('user').' as a ON u.id=a.user_cms_id '.$filters;
						$db->setQuery($query);
						$elements = $db->loadResult();
						$widget->elements =& $elements;
						return true;
					}
					if($widget->widget_params->customers=='best_customer'){
						$filters=array();
						$filters=$this->_dateLimit($widget, $filters, $date_field);
						$elements=$this->_getBestCustomers($filters, $widget, 'LIMIT 1');
						$widget->elements =& $elements[0]->name;
						return true;
					}
				}else{
					$date_field = 'a.order_'.@$widget->widget_params->date_type;
					$filters=$this->_dateLimit($widget, $filters, $date_field);
					if(!empty($filters)){
						$filters = (empty($filters)? ' ':' WHERE ').implode(' AND ',$filters);
					}
					if($widget->widget_params->content=='best'){ $reverse=false; }
					else{$reverse= true;	}
					switch($widget->widget_params->apply_on){
						case 'product':
							$products=$this->_getBestProducts($filters, 'table', 'LIMIT 1', $reverse, true);
							if(empty($products[0]->order_product_name)) $name=JText::_('NOT_SPECIFIED');
							else{ $name=$products[0]->order_product_name.' ('.$products[0]->Total.')'; }
							$widget->elements = $name;
							return true;
							break;
						case 'category':
							$categories=$this->_getBestCategories($filters, 'orders', 'LIMIT 1', $reverse);
							$categoryId=array_keys($categories);
							if(empty($categories)){
								$widget->elements=JText::_('NOT_SPECIFIED');
								return true;
							}
							$query='SELECT category_name FROM '.hikashop_table('category').' WHERE category_id='.(int)$categoryId[0];
							$db->setQuery($query);
							$elements = $db->loadResult();
							if(!isset($elements)){
								$elements=JText::_('NOT_SPECIFIED');
							}else{
								foreach($categories as $cat){
									$elements.=' ('.$cat->Total.')';
								}
							}
							$widget->elements = $elements;
							return true;
							break;
						case 'shipping_method':
							$shipping=$this->_getBestShipping($filters, 'LIMIT 1', $reverse);
							foreach($shipping as $ship){
								if(empty($ship->order_shipping_method)){ $name= JText::_('NOT_SPECIFIED'); }
								else{ $name=$ship->order_shipping_method;}
								$element=$name.' ('.$ship->Total.')';
							}
							$widget->elements = $element;
							return true;
							break;
						case 'payment_method':
							$payment=$this->_getBestPayment($filters, 'LIMIT 1', $reverse);
							foreach($payment as $pay){
								if(empty($pay->order_payment_method)){ $name= JText::_('NOT_SPECIFIED'); }
								else{ $name=$pay->order_payment_method;}
								$element=$name.' ('.$pay->Total.')';
							}
							$widget->elements = $element;
							return true;
							break;
						case 'currency':
							$currency=$this->_getBestCurrencies($filters, $limit, $reverse);
							foreach($currency as $cur){
								if(empty($cur->currency_name)){ $name= JText::_('NOT_SPECIFIED'); }
								else{ $name=$cur->currency_name; }
								$element=$name.' ('.$cur->Total.')';
							}
							$widget->elements = $element;
							return true;
							break;
						case 'discount':
							$filters=array();
							$filters=$this->_dateLimit($widget, $filters, $date_field);
							$filters[]='order_discount_code IS NOT NULL AND order_discount_code <> \'\'';
							$filters = (empty($filters)? ' ':' WHERE ').implode(' AND ',$filters);
							$discount = $this->_getBestDiscount($filters, ' LIMIT 1', $reverse);
							foreach($discount as $disc){
								$element=$disc->order_discount_code.' ('.$disc->Total.')';
							}
							$widget->elements = $element;
							return true;
							break;
						case 'country':
							break;
						default: break;
					}

				}
				if(empty($groupby)) $groupby = array();
				$groupby[] = 'a.order_currency_id';
				break;
			default:
				break;
		}

		$end=time();
		$filters = $this->_dateLimit($widget, $filters, $date_field);
		if(!empty($filters)){
			$filters = (empty($filters)? ' ':' WHERE ').implode(' AND ',$filters);
		} else {
			$filters = '';
		}
		$leftjoin = implode(' ',$leftjoin);
		if(!empty($groupby)){
			if(!empty($limit)){
				if(strpos($limit,'GROUP BY ')!==false){
					$limit = str_replace('GROUP BY ', 'GROUP BY '.implode(',',$groupby).',', $limit);
				}else{
					$limit = ' GROUP BY '.implode(',',$groupby).' '.$limit;
				}
			}else{
				$limit = ' GROUP BY '.implode(',',$groupby);
			}
		}
		$query=$select.$fieldtype.' FROM '.hikashop_table($table).' AS a'.$leftjoin.$filters.$limit;
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$elements = $db->loadObjectList();
		if(!empty($elements)){
			if(isset($widget->widget_params->compares)){
				foreach($elements as $element){
					if(empty($element->type)){
						$element->type=JText::_('NOT_SPECIFIED');
					}
				}
			}
		}

		if(!empty($elements)){
			$first = reset($elements);
			if($widget->widget_params->content=='sales' && isset($first->currency_id)){
				$currencyClass=hikashop_get('class.currency');
				$currencyClass->convertStats($elements);
				if($widget->widget_params->display=='pie'){
					$group = 'name';
				}elseif($widget->widget_params->display=='map'){
					$group = 'zone_code_2';
				}else{
					$group = 'calculated_date';
				}
				$newElements = array();
				foreach($elements as $k => $element){
					if(isset($widget->widget_params->compares)){
						$type=$element->type;
					}else{
						$type='';
					}
					if(!isset($newElements[$element->$group.$type])){
						$newElements[$element->$group.$type]=$element;
					}else{
						$newElements[$element->$group.$type]->total += $element->total;
					}
				}
				$elements = $newElements;
			}
		}

		switch($widget->widget_params->display){
			case 'gauge':
				if(empty($widget->widget_params->end)){
					$widget->widget_params->end=time();
				}
				$current = $this->_mysqlDate($widget->widget_params->date_group,$widget->widget_params->end);
				$total = 0.0;
				$main=0.0;
				$average = 0.0;
				$same = array();
				$i = 0;
				if(!empty($elements)){
					foreach($elements as $k => $period){
						if($period->calculated_date==$current){
							$main = $period->total;
						}else{
							$total+=$period->total;
							if(!isset($same[$period->calculated_date])){
								$i++;
								$same[$period->calculated_date]=$period->calculated_date;
							}
						}
					}
				}
				if($i){
					$average = $total/$i;
				}
				$widget->average = $average;
				$widget->total = $total;
				$widget->main = $main;
				$widget->exportFields = array('calculated_date','total');
				break;
			case 'map':
				$widget->exportFields = array('zone_code_2','zone_name_english','total');
				if(!empty($elements)){
					$newElements = array();
					foreach($elements as $k => $element){
						if(!empty($element->zone_code_2)){
							$newElements[$element->zone_code_2]=$element;
						}
					}
					$elements = $newElements;
				}
				break;
			case 'column':
			case 'line':
			case 'area':
			case 'graph':
				$dates = array();
				$minimum = 0;
				if(!empty($elements)){
					foreach($elements as $k => $element){

						$this->_jsDate($widget->widget_params->date_group,$element, $widget, $diff);
						if(!isset($element->type)){
							$element->type=$this->_generateName($widget, $widget->widget_params->content, $element);
						}
						if(empty($minimum) || $minimum>$element->timestamp){
							$minimum = $element->timestamp;
						}
						$dates[$element->calculated_date.$element->type] = $element;
					}
				}

				if(empty($widget->widget_params->end)){
					$widget->widget_params->end=time();
				}
				if(empty($widget->widget_params->start)){
					if(!empty($widget->widget_params->period)){
						$widget->widget_params->start = $widget->widget_params->end - $widget->widget_params->period;
					}else{
						if($minimum==0){
							$minimum = time();
						}
						$widget->widget_params->start = $minimum;
					}
				}

				$typeTable=array();
				if(!empty($elements)){
					foreach($elements as $element){
						if(!isset($typeTable[$element->type])){
							$typeTable[$element->type]=$element->type;
						}
					}
				}

				$widget->widget_params->end;
				$end = $widget->widget_params->end;
				$obj = new stdClass();
				$obj->timestamp = $end;
				$obj->calculated_date = $this->_mysqlDate($widget->widget_params->date_group,$obj->timestamp);
				if(isset($widget->widget_params->compares)){
						foreach($typeTable as $oneType){
							$obj = new stdClass();
							$obj->timestamp = $end;
							$obj->calculated_date = $this->_mysqlDate($widget->widget_params->date_group,$obj->timestamp);
							if(!isset($dates[$obj->calculated_date.$oneType])){
								$this->_jsDate($widget->widget_params->date_group,$obj, $widget, $diff);
								$obj->total = 0;
								$obj->type=$this->_generateName($widget,$oneType, $obj);
								$dates[$obj->calculated_date.$oneType] = $obj;
							}
						}
				}else{
					if(!isset($dates[$obj->calculated_date.$widget->widget_params->content])){
						$this->_jsDate($widget->widget_params->date_group,$obj, $widget, $diff);
						$obj->total = 0;
						$obj->type=$this->_generateName($widget, $widget->widget_params->content, $obj);
						$dates[$obj->calculated_date.$widget->widget_params->content] = $obj;
					}
				}

				while($widget->widget_params->start<$end){
					switch($widget->widget_params->date_group){
						case '%H %j %Y':
							$end = $end-3600;
							break;
						case '%j %Y':
							$end = strtotime("-1 day", $end);
							break;
						case '%u %Y':
							$end = strtotime("-1 week", $end);
							break;
						case '%m %Y':
							$end = strtotime("-1 month", $end);
							break;
						case '%Y':
							$end = strtotime("-1 year", $end);
							break;
						default:
							$end = strtotime("-1 year", $end);
							break;
					}

					$obj = new stdClass();
					$obj->timestamp = $end;
					$obj->calculated_date = $this->_mysqlDate($widget->widget_params->date_group,$obj->timestamp);
					if(isset($widget->widget_params->compares) && !empty($elements)){
						foreach($typeTable as $oneType){
							$obj = new stdClass();
							$obj->timestamp = $end;
							$obj->calculated_date = $this->_mysqlDate($widget->widget_params->date_group,$obj->timestamp);
							if(!isset($dates[$obj->calculated_date.$oneType])){
								$this->_jsDate($widget->widget_params->date_group,$obj, $widget, $diff);
								$obj->total = 0;
								$obj->type=$this->_generateName($widget,$oneType, $obj);
								$dates[$obj->calculated_date.$oneType]=$obj;
							}
						}

					}else{
						if(!isset($dates[$obj->calculated_date.$widget->widget_params->content])){
							$this->_jsDate($widget->widget_params->date_group,$obj, $widget, $diff);
							$obj->total = 0;
							$obj->type=$this->_generateName($widget, $widget->widget_params->content, $obj);
							$dates[$obj->calculated_date.$widget->widget_params->content]=$obj;
						}
					}
				}

				$obj = new stdClass();
				$obj->timestamp = $widget->widget_params->start;
				$obj->calculated_date = $this->_mysqlDate($widget->widget_params->date_group,$obj->timestamp);
				$obj->calculated_date = $this->_mysqlDate($widget->widget_params->date_group,$obj->timestamp);
				if(isset($widget->widget_params->compares)){
					foreach($typeTable as $oneType){
						$obj = new stdClass();
						$obj->timestamp = $end;
						$obj->calculated_date = $this->_mysqlDate($widget->widget_params->date_group,$obj->timestamp);
						if(!isset($dates[$obj->calculated_date.$oneType])){
							$this->_jsDate($widget->widget_params->date_group,$obj, $widget, $diff);
							$obj->type=$this->_generateName($widget,$oneType, $obj);
							$dates[$obj->calculated_date.$oneType]=$obj;
						}
					}
				}else{
					if(!isset($dates[$obj->calculated_date.$widget->widget_params->content])){
						$this->_jsDate($widget->widget_params->date_group,$obj, $widget, $diff);
						$obj->total = 0;
						$obj->type=$this->_generateName($widget, $widget->widget_params->content, $obj);
						$dates[$obj->calculated_date]=$obj;
					}
				}

				$elements = array();
				foreach($dates as $date){
					if(isset($date->type)){ $type=$date->type;	}
					else{ $type=''; }
					if(!isset($elements[$date->calculated_date.$type])){
						$elements[$date->calculated_date.$type]=$date;
					}
					if(empty($date->total)){
						$date->total=0;
					}
				}
				ksort($elements);
				if(isset($widget->widget_params->compares)){
					$widget->exportFields = array('type','calculated_date','total');
				}else{
					$widget->exportFields = array('calculated_date','total');
				}
				break;
			case 'pie':
				$name = 'name';
				$widget->exportFields = array('name','total');
			case 'listing':
				if($widget->widget_params->display=='listing'){
					$name = 'order_status';
					if(!empty($elements)){
						$first = reset($elements);
						unset($first->user_params);
						$widget->exportFields = array_keys(get_object_vars($first));
					}else{
						$widget->exportFields=array();
					}
				}
				if(($widget->widget_params->content=='products' || $widget->widget_params->content=='categories') && $widget->widget_params->product_data=='orders'){
					foreach($elements as $element){
						$element->quantity=$element->Total;
						$element->Total=$element->quantity*$element->order_product_price;

					}
				}
				$class = hikashop_get('class.category');
				$trans = $class->loadAllWithTrans('status');
				if(!empty($elements)){
					foreach($elements as $k => $element){
						if(!empty($element->$name)){
							$found = false;
							if(!empty($trans)){
								foreach($trans as $t){
									if($t->category_name == $element->$name && isset($t->translation)){
										$elements[$k]->$name = $t->translation;
										$found = true;
									}
								}
							}
							if(!$found){
								$fileTrans = JText::_(strtoupper($element->$name));
								if($fileTrans != strtoupper($element->$name)){
									$elements[$k]->$name = $fileTrans;
								}
							}
						}
					}
				}
				break;
			case 'table':
				if(empty($elements)){
					$total = new stdClass();
					$total->Total=0;
					$total->order_currency_id = hikashop_getCurrency();
					$elements = array($total);
				}
				if($widget->widget_params->content=='sales' || $widget->widget_params->content=='taxes'){
					$currencyClass = hikashop_get('class.currency');
					$main_currency = reset($elements);
					$total = 0;
					foreach($elements as $element){
						if($element->order_currency_id!=$main_currency->order_currency_id){
							$total += $currencyClass->convertUniquePrice($element->Total,$element->order_currency_id, $main_currency->order_currency_id);
						}else{
							$total += $element->Total;
						}
					}
					$value=$currencyClass->format($total,$main_currency->order_currency_id);
				}else{
					$value=$elements[0]->Total.' '.JText::_('orders');
				}
				$elements=$value;
				break;
			default:
				break;
		}

		if(isset($widget->widget_params->period_compare) && $widget->widget_params->period_compare!='null' && isset($widget->elements)){
			switch($widget->widget_params->period_compare){
				case 'last_period':
					$widget->widget_params->start=$widget->widget_params->end;
					$widget->widget_params->end=$widget->widget_params->start+$diff;
					break;
				case 'last_year':
					$widget->widget_params->end=strtotime("+1 year", $widget->widget_params->end);
					$widget->widget_params->start=strtotime("+1 year", $widget->widget_params->start);
					break;
				case 'average':
					$widget->widget_params->end='';
					$widget->widget_params->start='';
					break;
			}
		}

		if(isset($widget->widget_params->period_compare) && $widget->widget_params->period_compare=='last_year' && isset($widget->elements)){
			foreach($elements as $element){
				$element->year=$element->year+1;
			}
		}

		if(!isset($widget->elements)){
			$widget->elements =& $elements;
		}else{
			$widget->elements= array_merge($widget->elements,$elements);
		}
		return true;
	}

	function _generateName($widget, $varName, $obj){
		$date='';
		$type='';
		if(isset($widget->widget_params->period_compare)){
			switch($widget->widget_params->period_compare){
				case 'last_period':
					$date=' - '.JText::_('LAST_PERIOD');
					break;
				case 'last_year':
					$date=' - '.JText::_('LAST_YEAR');
					break;
			}
		}
		if(isset($widget->widget_params->period_compare) && isset($widget->elements)){
			$type=$varName.$date;
		}else{
			$type=$varName;
		}
		return $type;
	}

	function _getBestDiscount($filters, $limit='', $reverse=false, $leftjoin=''){
		$order='DESC';
		if($reverse){ $order= 'ASC'; }
		$db = JFactory::getDBO();
		$query='SELECT *,COUNT(a.order_id) as Total FROM '.hikashop_table('order').' as a	LEFT JOIN '.hikashop_table('order_product').' as p on p.order_id=a.order_id ' .
					'LEFT JOIN '.hikashop_table('discount').' as d ON d.discount_code=a.order_discount_code '.$leftjoin.' '.$filters.' GROUP BY order_discount_code ORDER BY Total '.$order.' '.$limit;
		$db->setQuery($query);
		$discountCodes = $db->loadObjectList('order_discount_code');
		return $discountCodes;
	}

	function _getBestShipping($filters, $limit='', $reverse=false){
		$order='DESC';
		if($reverse){ $order= 'ASC'; }
		$db = JFactory::getDBO();
		$query='SELECT *,SUM(order_product_quantity) as Total FROM '.hikashop_table('order').' as a	LEFT JOIN '.hikashop_table('order_product').' as p on p.order_id=a.order_id ' .
				' '.$filters.' GROUP BY order_shipping_method ORDER BY Total '.$order.' '.$limit;
		$db->setQuery($query);
		$shippingMethods = $db->loadObjectList('order_shipping_method');
		return $shippingMethods;
	}

	function _getBestPayment($filters, $limit='', $reverse=false){
		$order='DESC';
		if($reverse){ $order= 'ASC'; }
		$db = JFactory::getDBO();
		$query='SELECT *,SUM(order_product_quantity) as Total FROM '.hikashop_table('order').' as a	LEFT JOIN '.hikashop_table('order_product').' as p on p.order_id=a.order_id ' .
				' '.$filters.' GROUP BY order_payment_method ORDER BY Total '.$order.' '.$limit;
		$db->setQuery($query);
		$shippingMethods = $db->loadObjectList('order_payment_method');
		return $shippingMethods;
	}

	function _getBestCategories($filters, $content='', $limit='', $reverse=false){
		$db = JFactory::getDBO();
		$order='DESC';
		if($reverse){ $order= 'ASC'; }
		if($content=='sales'){
			$dataType='order_full_price';
		}else if($content=='clicks'){
			$dataType='p.product_hit';
		}else{
			$dataType='order_product_quantity';
		}
		if(empty($filters)){ $filters='';}
		$query='SELECT *, SUM('.$dataType.') as Total FROM '.hikashop_table('order').' as a LEFT JOIN '.hikashop_table('order_product ').' as prod ON a.order_id=prod.order_id LEFT JOIN '.hikashop_table('product').' AS p ON p.product_id=prod.product_id ' .
				'LEFT JOIN '.hikashop_table('product_category ').' as cat ON cat.product_id=p.product_id OR cat.product_id=p.product_parent_id LEFT JOIN '.hikashop_table('category ').' as c ON cat.category_id=c.category_id ' .
				$filters.'	GROUP BY c.category_id ORDER BY Total '.$order.' '.$limit;
		$db->setQuery($query);
		$ids = $db->loadObjectList('category_id');
		if(!empty($ids)){
			if($content=='orders' && $limit!='LIMIT 1'){
				foreach($ids as $id){
					$id->quantity=$id->Total;
					$id->Total=$id->quantity*$id->order_product_price;
				}
			}
		}
		return $ids;
	}

	function _getBestProducts($filters, $content, $limit='', $reverse=false, $statusForce=false, $leftjoin=''){
		$db = JFactory::getDBO();
		$order='DESC';
		$dataType='';
		if($reverse){ $order= 'ASC'; }
		if($content=='sales'){
			$dataType='SUM(prod.order_product_price*prod.order_product_quantity)';
		}else if($content=='clicks'){
			$dataType='p.product_hit';
			if(strpos($leftjoin,'hikashop_product AS p ON ')===false)
				$leftjoin.=' LEFT JOIN '.hikashop_table('product').' AS p ON p.product_id=prod.product_id ';
		}else{
			$dataType='SUM(prod.order_product_quantity)';
		}
		if($statusForce && !empty($filters)){
			$filters.=" AND a.order_status IN ('confirmed','shipped')";
		}else if($statusForce && empty($filters)){
			$filters=" WHERE a.order_status IN ('confirmed','shipped')";
		}
		$leftjoin=str_replace('LEFT JOIN '.hikashop_table('order_product').' AS prod ON prod.order_id = a.order_id','',$leftjoin);
		$query='SELECT *, '.$dataType.' as Total, COUNT(a.order_id) AS quantity FROM '.hikashop_table('order_product').' AS prod LEFT JOIN '.hikashop_table('order').' AS a on a.order_id=prod.order_id '
			.$leftjoin.' '.$filters.' GROUP BY prod.product_id ORDER BY Total '.$order.' '.$limit;
		$db->setQuery($query);
		$ids = $db->loadObjectList();
		if(!empty($ids)){
			if($content=='orders'){
				foreach($ids as $id){
					$id->quantity=$id->Total;
					$id->Total=$id->quantity*$id->order_product_price;
				}
			}
		}
		return $ids;
	}

	function _getBestCurrencies($filters, $limit='', $reverse=false){
		$db = JFactory::getDBO();
		$order='ASC';
		if($reverse){ $order= 'DESC'; }
		$query='SELECT *, SUM(a.order_id) as Total FROM '.hikashop_table('order ').' AS a LEFT JOIN '.hikashop_table('currency ').' AS c ON a.order_currency_id=c.currency_id ' .
				$filters.' GROUP BY c.currency_id ORDER BY Total '.$order.' '.$limit.'';
		$db->setQuery($query);
		$currencies = $db->loadObjectList();
		return $currencies;
	}

	function _getBestCustomers($filters, $widget, $limit=''){
		$db = JFactory::getDBO();

		if($widget->widget_params->content=='customers'){
			$unitType='o.order_full_price';
			$currencyID='o.order_currency_id';
			$user_id='o.order_user_id';
		}else{
			$unitType='o.order_partner_price';
			$currencyID='o.order_partner_currency_id';
			$user_id='o.order_partner_id';
		}

		if($widget->widget_params->display=='table'){
			$orderBy='Total';
		}else{
			if(($widget->widget_params->content=='customers' && $widget->widget_params->customers_order=='sales') || ($widget->widget_params->content=='partners' && $widget->widget_params->partners_order=='sales')){
				$orderBy='Total';
			}else{
				$orderBy='order_number';
			}
		}
		$case=' case';
		$currentCurrency = hikashop_getCurrency();
		$currencyType = hikashop_get('type.currency');
		$currencyClass = hikashop_get('class.currency');
		$dstCurrency = $currencyClass->get($currentCurrency);
		$currencyType->load(0);
		$currencies = $currencyType->currencies;
		$config =& hikashop_config();
		$main_currency = $config->get('main_currency',1);
		foreach($currencies as $currency){
			$calculatedVal=$unitType;
			if($main_currency!=$currency->currency_id){
				if(bccomp($currency->currency_percent_fee,0,2)){
					$calculatedVal.='*'.floatval($currency->currency_percent_fee)/100.0;
				}
				$calculatedVal.='/'.floatval($currency->currency_rate);
			}
			if($main_currency!=$currentCurrency){
				$calculatedVal.='*'.floatval($dstCurrency->currency_rate);
				if(bccomp($dstCurrency->currency_percent_fee,0,2)){
					$calculatedVal.='*'.floatval($dstCurrency->currency_percent_fee)/100.0;
				}
			}
			$case .= ' when '.$currencyID.' = \''.$currency->currency_id.'\' then '.$calculatedVal;
		}
		$case.= ' end';

		$filters[]=' a.user_id IS NOT NULL ';
		$filters[]=' o.order_type=\''.$this->order_type.'\'';
		$filters = (empty($filters)? ' ':' WHERE ').implode(' AND ',$filters);
		$query='SELECT *,	SUM( '.$case.' ) AS Total, COUNT(o.order_id) AS order_number FROM '.hikashop_table('order').' as o LEFT JOIN '.hikashop_table('user').' as a ON '.$user_id.'=a.user_id	LEFT JOIN '.hikashop_table('users',false).' as u ON u.id=a.user_cms_id ' .
				$filters.' GROUP BY a.user_id ORDER BY '.$orderBy.' DESC '.$limit;
		$db->setQuery($query);
		$elements = $db->loadObjectList();
		if(empty($elements))
			$elements = array();
		foreach($elements as $element){
			$element->order_currency_id=$main_currency;
		}
		return $elements;
	}

	function _mysqlDate($group,$date){
		$current_year=date('Y',$date);
		switch($group){
			case '%H %j %Y':
				$current_hour=date('H',$date);
				$current_day = sprintf('%03d',date('z',$date)+1);
				$current = $current_year.' '.$current_day.' '.$current_hour;
				break;
			case '%j %Y':
				$current_day = sprintf('%03d',date('z',$date)+1);
				$current = $current_year.' '.$current_day;
				break;
			case '%u %Y':
				$current_week = sprintf('%02d',date('W',$date));
				$current = $current_year.' '.$current_week;
				break;
			case '%m %Y':
				$current_month = date('m',$date);
				$current=$current_year.' '.$current_month;
				break;
			case '%Y':
				$current=$current_year;
				break;
			default:
				$current='';
				break;
		}
		return $current;
	}

	function _jsDate($group,&$element, $widget=null, $diff=null){
		if(!isset($element->timestamp)){
			switch($group){
				case '%H %j %Y'://day
					$parts = explode(' ',$element->calculated_date);
					$element->timestamp = mktime($parts[2], 0, 0, 1, $parts[1], $parts[0]);

					break;
				case '%j %Y'://day
					$parts = explode(' ',$element->calculated_date);
					$element->timestamp = mktime(0, 0, 0, 1, $parts[1], $parts[0]);
					break;
				case '%u %Y'://week
					$parts = explode(' ',$element->calculated_date);
					$element->timestamp = mktime(0, 0, 0, 1, $parts[1]*7, $parts[0]);
					break;
				case '%m %Y'://month
					$parts = explode(' ',$element->calculated_date);
					$element->timestamp = mktime(0, 0, 0, $parts[1], 1, $parts[0]);
					break;
				case '%Y'://year
					$element->timestamp = mktime(0, 0, 0, 1, 1, $element->calculated_date);
					break;
			}
		}

		if(isset($widget->widget_params->period_compare)){
			if($widget->widget_params->period_compare=='last_period' && isset($widget->elements)){
					$element->timestamp=$element->timestamp+$diff;
			}
		}

		$element->year = date('Y',$element->timestamp);
		$element->month = date('m',$element->timestamp)-1;
		$element->day = date('j',$element->timestamp);
		if($widget->widget_params->date_group=="%H %j %Y"){
			$element->hour = date('G',$element->timestamp);
		}
	}

	function _dateLimit($widget, $filters, $date_field){
		if(empty($date_field))
			return $filters;
		if(!empty($widget->widget_params->start)){
			$filters['start']=$date_field.' > '.$widget->widget_params->start;
		}
		if(!empty($widget->widget_params->end)){
			$filters['end']=$date_field.' < '.$widget->widget_params->end;
			$end = $widget->widget_params->end;
		}

		if((empty($filters['start']) || empty($filters['end'])) && !empty($widget->widget_params->period)){
			if(!empty($filters['start'])){
				$filters['end']=$date_field.' < '.($widget->widget_params->start+$widget->widget_params->period);
			}else{
				if(!isset($end)){ $end=time(); }
				$filters['start']=$date_field.' > '.($end-$widget->widget_params->period);
			}
		}
		return $filters;
	}
}
