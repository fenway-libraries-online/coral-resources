<?php

		$resourceID = $_GET['resourceID'];
		if ($resourceID){
		$resource = new Resource(new NamedArguments(array('primaryKey' => $resourceID)));
		}else{
			$resource = new Resource();
		}

		//used for default currency
		$config = new Configuration();

		//get all acquisition types for output in drop down
		$acquisitionTypeArray = array();
		$acquisitionTypeObj = new AcquisitionType();
		$acquisitionTypeArray = $acquisitionTypeObj->sortedArray();

		//get all resource formats for output in drop down
		$resourceFormatArray = array();
		$resourceFormatObj = new ResourceFormat();
		$resourceFormatArray = $resourceFormatObj->sortedArray();

		//get all resource types for output in drop down
		$resourceTypeArray = array();
		$resourceTypeObj = new ResourceType();
		$resourceTypeArray = $resourceTypeObj->allAsArray();


		//get all currency for output in drop down
		$currencyArray = array();
		$currencyObj = new Currency();
		$currencyArray = $currencyObj->allAsArray();

		//get all Order Types for output in drop down
		$orderTypeArray = array();
		$orderTypeObj = new OrderType();
		$orderTypeArray = $orderTypeObj->allAsArray();

		//get all Cost Details for output in drop down
		$costDetailsArray = array();
		$costDetailsObj = new CostDetails();
		$costDetailsArray = $costDetailsObj->allAsArray();

		//get payments
		$paymentArray = array();
		if ($resourceID){
			$sanitizedInstance = array();
			$instance = new ResourcePayment();
			foreach ($resource->getResourcePayments() as $instance) {
				foreach (array_keys($instance->attributeNames) as $attributeName) {
					$sanitizedInstance[$attributeName] = $instance->$attributeName;
				}

				$sanitizedInstance[$instance->primaryKeyName] = $instance->primaryKey;

				array_push($paymentArray, $sanitizedInstance);
			}
		}

		//get notes
		if ($resourceID){
			$resourceNote = $resource->getInitialNote;
		}else{
			$resourceNote = new ResourceNote();
		}

		$orgArray = $resource->getOrganizationArray();
		if (count($orgArray)>0){
			foreach ($orgArray as $org){
				$providerText = $org['organization'];
				$orgID = $org['organizationID'];
			}
		}else{
			$providerText = $resource->providerText;
			$orgID = '';
		}
?>
		<div id='div_resourceSubmitForm'>
		<form id='resourcePromptForm'>


		<input type='hidden' id='organizationID' value='<?php echo $orgID; ?>' />
		<input type='hidden' id='editResourceID' value='<?php echo $resourceID; ?>' />
		<div class='formTitle' style='width:745px;'><span class='headerText'><?php if ($resourceID) { echo "Edit Saved Resource"; }else{ echo "Add New Resource"; } ?></span></div>
		<div class='smallDarkRedText' style='height:14px;margin:3px 0px 0px 0px;'>&nbsp;* required fields</div>

		<table class='noBorder'>
		<tr style='vertical-align:top;'>
		<td style='vertical-align:top; padding-right:35px;'>

			<span class='surroundBoxTitle'>&nbsp;&nbsp;<b>Product</b>&nbsp;&nbsp;</span>

			<table class='surroundBox' style='width:350px;'>
			<tr>
			<td>

				<table class='noBorder' style='width:310px; margin:5px 15px;'>

					<tr>
					<td style='vertical-align:top;text-align:left;'><label for='titleText'>Name:&nbsp;&nbsp;<span class='bigDarkRedText'>*</span></label></td>
					<td><input type='text' id='titleText' style='width:220px;' class='changeInput' value="<?php echo $resource->titleText; ?>" /><span id='span_error_titleText' class='smallDarkRedText'></span></td>
					</tr>

					<tr>
					<td style='vertical-align:top;text-align:left;'><label for='descriptionText'>Description:</label></td>
					<td><textarea rows='3' id='descriptionText' style='width:223px'><?php echo $resource->descriptionText; ?></textarea></td>
					</tr>

					<tr>
					<td style='vertical-align:top;text-align:left;'><label for='providerText'>Provider:</label></td>
					<td><input type='text' id='providerText' style='width:220px;' class='changeInput' value='<?php echo $providerText; ?>' /><span id='span_error_providerText' class='smallDarkRedText'></span></td>
					</tr>

					<tr>
					<td style='vertical-align:top;text-align:left;'><label for='resourceURL'>URL:</label></td>
					<td><input type='text' id='resourceURL' style='width:220px;' class='changeInput' value='<?php echo $resource->resourceURL; ?>' /><span id='span_error_resourceURL' class='smallDarkRedText'></span></td>
					</tr>

					<tr>
					<td style='vertical-align:top;text-align:left;'><label for='resourceAltURL'>Alt URL:</label></td>
					<td><input type='text' id='resourceAltURL' style='width:220px;' class='changeInput' value='<?php echo $resource->resourceAltURL; ?>' /><span id='span_error_resourceAltURL' class='smallDarkRedText'></span></td>
					</tr>

				</table>
			</td>
			</tr>
			</table>



			<span class='surroundBoxTitle'>&nbsp;&nbsp;<label for='resourceFormatID'><b>Format</b></label>&nbsp;<span class='bigDarkRedText'>*</span>&nbsp;&nbsp;</span>

			<table class='surroundBox' style='width:350px;'>
			<tr>
			<td>

				<table class='noBorder' style='width:310px; margin:5px 15px;'>
				<?php
				$i=0;

				foreach ($resourceFormatArray as $resourceFormat){
					$i++;
					if(($i % 2)==1){
						echo "<tr>\n";
					}

					//determine default
					if ($resourceID){
						if ($resourceFormat['resourceFormatID'] == $resource->resourceFormatID) $checked = 'checked'; else $checked = '';
					//otherwise default to electronic
					}else{
						if (strtoupper($resourceFormat['shortName']) == 'ELECTRONIC') $checked = 'checked'; else $checked = '';
					}

					echo "<td><input type='radio' name='resourceFormatID' id='resourceFormatID' value='" . $resourceFormat['resourceFormatID'] . "' " . $checked . " />  " . $resourceFormat['shortName'] . "</td>\n";

					if(($i % 2)==0){
						echo "</tr>\n";
					}
				}

				if(($i % 2)==1){
					echo "<td>&nbsp;</td></tr>\n";
				}

				?>

				</table>

			</td>
			</tr>
			</table>


			<span class='surroundBoxTitle'>&nbsp;&nbsp;<b>Acquisition Type</b>&nbsp;<span class='bigDarkRedText'>*</span>&nbsp;&nbsp;</span>

			<table class='surroundBox' style='width:350px;'>
			<tr>
			<td>

				<table class='noBorder smallPadding' style='width:310px; margin:5px 15px;'>
				<?php
				$i=0;

				foreach ($acquisitionTypeArray as $acquisitionType){
					$i++;
					if(($i % 3)==1){
						echo "<tr>\n";
					}

					//set default
					if ($resourceID){
						if ($acquisitionType['acquisitionTypeID'] == $resource->acquisitionTypeID) $checked = 'checked'; else $checked = '';
					}else{
						if (strtoupper($acquisitionType['shortName']) == 'PAID') $checked = 'checked'; else $checked = '';
					}

					echo "<td><input type='radio' name='acquisitionTypeID' id='acquisitionTypeID' value='" . $acquisitionType['acquisitionTypeID'] . "' " . $checked . " />  " . $acquisitionType['shortName'] . "</td>\n";

					if(($i % 3)==0){
						echo "</tr>\n";
					}
				}

				if(($i % 3)==1){
					echo "<td>&nbsp;</td><td>&nbsp;</td></tr>\n";
				}else if(($i % 3)==2){
					echo "<td>&nbsp;</td></tr>\n";
				}

				?>

				</table>

			</td>
			</tr>
			</table>

		</td>
		<td>

			<span class='surroundBoxTitle'>&nbsp;&nbsp;<label for='resourceFormatID'><b>Initial Cost</b></label>&nbsp;&nbsp;</span>

			<table class='surroundBox' style='width:350px;'>
			<tr>
			<td>

				<table class='noBorder smallPadding newPaymentTable' style='width:500px;margin:7px 15px 0px 15px;'>
				<tr>
					<td style='vertical-align:top;text-align:left;font-weight:bold;'>Year</td>
					<td style='vertical-align:top;text-align:left;font-weight:bold;'>Sub Start</td>
					<td style='vertical-align:top;text-align:left;font-weight:bold;'>Sub End</td>
					<td style='vertical-align:top;text-align:left;font-weight:bold;'>Fund</td>
					<td style='vertical-align:top;text-align:left;font-weight:bold;' colspan='2'>Payment</td>
					<td style='vertical-align:top;text-align:left;font-weight:bold;'>Type</td>
					<td style='vertical-align:top;text-align:left;font-weight:bold;'>Cost Details</td>
					<td style='vertical-align:top;text-align:left;font-weight:bold;'>Notes</td>
					<td style='vertical-align:top;text-align:left;font-weight:bold;'>Invoice</td>
					<td>&nbsp;</td>
				</tr>

				<tr class='newPaymentTR'>

				<td style='vertical-align:top;text-align:left;background:white;'>
		<input type='text' value = '' style='width:30px;' class='changeDefaultWhite changeInput year' />
		</td>

				<td style='vertical-align:top;text-align:left;background:white;'>
		<input type='text' value = '' style='width:60px;' class='changeDefaultWhite changeInput fundName' />
		</td>

				<td style='vertical-align:top;text-align:left;background:white;'>
		<input type='text' value = '' style='width:50px;' class='changeDefaultWhite changeInput paymentAmount' />
		</td>

				<td style='vertical-align:top;text-align:left;'>
					<select style='width:50px; padding:0px; margin:0px;' class='changeSelect currencyCode'>
					<?php
					foreach ($currencyArray as $currency){
						if ($currency['currencyCode'] == $config->settings->defaultCurrency){
							echo "<option value='" . $currency['currencyCode'] . "' selected class='changeSelect'>" . $currency['currencyCode'] . "</option>\n";
						}else{
							echo "<option value='" . $currency['currencyCode'] . "' class='changeSelect'>" . $currency['currencyCode'] . "</option>\n";
						}
					}
					?>
					</select>
				</td>
				<td style='vertical-align:top;text-align:left;'>
					<select style='width:70px;' class='changeSelect orderTypeID'>
					<option value='' selected></option>
					<?php
					foreach ($orderTypeArray as $orderType){
						echo "<option value='" . $orderType['orderTypeID'] . "'>" . $orderType['shortName'] . "</option>\n";
					}
					?>
					</select>
				</td>

				<td style='vertical-align:top;text-align:left;background:white;'>
		<input type='text' value = '' style='width:60px;' class='changeDefaultWhite changeInput costNote' />
		</td>

				<td style='vertical-align:center;text-align:left;width:37px;'>
		<a href='javascript:void();'><img src='images/add.gif' class='addPayment' alt='add this payment' title='add payment'></a>
		</td>
				</tr>

				</table>

				<table class='noBorder smallPadding paymentTable' style='width:320px;margin:7px 15px;'>

				<tr>
				<td colspan='5'>
				<div class='smallDarkRedText' id='div_errorPayment' style='margin:0px 20px 0px 26px;'></div>

				<hr style='width:480px;margin:0px 0px 5px 5px;' />
				</td>
				</tr>

				<?php
				if (count($paymentArray) > 0){
					foreach ($paymentArray as $payment){
				?>
						<tr>
						<td style='vertical-align:top;text-align:left;'>
			<input type='text' value = '<?php echo $payment['year']; ?>' style='width:40px;' class='changeInput year' /></td>

						<td style='vertical-align:top;text-align:left;'>
			<input type='text' value = '<?php echo $payment['subscriptionStartDate']; ?>' style='width:40px;' class='changeInput subscriptionStartDate' /></td>

						<td style='vertical-align:top;text-align:left;'>
			<input type='text' value = '<?php echo $payment['subscriptionEndDate']; ?>' style='width:40px;' class='changeInput subscriptionEndDate' /></td>

						<td style='vertical-align:top;text-align:left;'>
			<input type='text' value = '<?php echo $payment['fundName']; ?>' style='width:60px;' class='changeInput fundName' /></td>

						<td style='vertical-align:top;text-align:left;'>
			<input type='text' value = '<?php echo integer_to_cost($payment['paymentAmount']); ?>' style='width:50px;' class='changeInput paymentAmount' /></td>

						<td style='vertical-align:top;text-align:left;'>
			<input type='text' value = '<?php echo $payment['costNote']; ?>' style='width:60px;' class='changeInput costNote' /></td>

						<td style='vertical-align:top;text-align:left;'>
							<select style='width:50px;' class='changeSelect currencyCode'>
							<?php
							foreach ($currencyArray as $currency){
								if ($currency['currencyCode'] == $payment['currencyCode']){
									echo "<option value='" . $currency['currencyCode'] . "' selected class='changeSelect'>" . $currency['currencyCode'] . "</option>\n";
								}else{
									echo "<option value='" . $currency['currencyCode'] . "' class='changeSelect'>" . $currency['currencyCode'] . "</option>\n";
								}
							}
							?>
							</select>
						</td>

						<td style='vertical-align:top;text-align:left;'>
						<select style='width:70px;' class='changeSelect orderTypeID'>
						<option value=''></option>
						<?php
						foreach ($orderTypeArray as $orderType){
							if (!(trim(strval($orderType['orderTypeID'])) != trim(strval($payment['orderTypeID'])))){
								echo "<option value='" . $orderType['orderTypeID'] . "' selected class='changeSelect'>" . $orderType['shortName'] . "</option>\n";
							}else{
								echo "<option value='" . $orderType['orderTypeID'] . "' class='changeSelect'>" . $orderType['shortName'] . "</option>\n";
							}
						}
						?>
						</select>
						</td>

			<td style='vertical-align:top;text-align:left;'>
			<input type='text' value = '<?php echo $payment['details']; ?>' style='width:60px;' class='changeInput details' /></td>

			<td style='vertical-align:top;text-align:left;'>
			<select style='width:70px;' class='changeSelect costDetailsID'>
			<option value='' selected></option>
			<?php
			foreach ($costDetailsArray as $costDetails){
				echo "<option value='" . $costDetails['costDetailsID'] . "'>" . $costDetails['shortName'] . "</option>\n";
			}
			?>
			</select>
			</td>
			<td style='vertical-align:top;text-align:left;'>
			<input type='text' value = '<?php echo $payment['notes']; ?>' style='width:60px;' class='changeInput notes' /></td>
			<td style='vertical-align:top;text-align:left;'>
			<input type='text' value = '<?php echo $payment['invoice']; ?>' style='width:50px;' class='changeInput invoice' /></td>
			<td style='vertical-align:top;text-align:center;width:37px;'>
				<a href='javascript:void();'><img src='images/cross.gif' alt='remove this payment' title='remove this payment' class='remove' /></a>
			</td>
			</tr>
					<?php
					}
				}
		?>
				</table>
			</td>
			</tr>
			</table>


			<span class='surroundBoxTitle'>&nbsp;&nbsp;<label for='resourceTypeID'><b>Resource Type</b></label>&nbsp;&nbsp;</span>

			<table class='surroundBox' style='width:350px;'>
			<tr>
			<td>

				<table class='noBorder' style='width:320px; margin:5px 15px;'>
				<?php
				$i=0;

				foreach ($resourceTypeArray as $resourceType){
					$i++;
					if(($i % 3)==1){
						echo "<tr>\n";
					}

					$checked='';
					//determine default checked
					if ($resourceID){
						if (strtoupper($resourceType['resourceTypeID']) == $resource->resourceTypeID) $checked = 'checked';
					}

					echo "<td><input type='radio' name='resourceTypeID' id='resourceTypeID' value='" . $resourceType['resourceTypeID'] . "' " . $checked . "/>" . $resourceType['shortName'] . "</td>\n";

					if(($i % 3)==0){
						echo "</tr>\n";
					}
				}

				if(($i % 3)==1){
					echo "<td>&nbsp;</td><td>&nbsp;</td></tr>\n";
				}else if(($i % 3)==2){
					echo "<td>&nbsp;</td></tr>\n";
				}

				?>

				</table>
				<span id='span_error_resourceTypeID' class='smallDarkRedText'></span>

			</td>
			</tr>
			</table>



			<span class='surroundBoxTitle'>&nbsp;&nbsp;<label for='resourceFormatID'><b>Notes</b></label>&nbsp;&nbsp;</span>

			<table class='surroundBox' style='width:350px;'>
			<tr>
			<td>

				<table class='noBorder smallPadding' style='width:320px; margin:7px 15px;'>

					<tr>
					<td style='vertical-align:top;text-align:left;'><span class='smallGreyText'>Include any additional information</span><br />
					<textarea rows='5' id='noteText' name='noteText' style='width:310px'><?php echo $resourceNote->noteText; ?></textarea></td>
					</tr>
				</table>
			</td>
			</tr>
			</table>

		</td>
		</tr>
		</table>

		<hr style='width:745px;margin:15px 0px 10px 0px;' />

		<table class='noBorderTable' style='width:175px;'>
			<tr>
				<td style='text-align:left'><input type='button' value='save' class='submitResource' id ='save'></td>
				<td style='text-align:left'><input type='button' value='submit' class='submitResource' id ='progress'></td>
				<td style='text-align:left'><input type='button' value='cancel' onclick="kill(); tb_remove()"></td>
			</tr>
		</table>


		</form>
		</div>

		<script type="text/javascript" src="js/forms/resourceNewForm.js?random=<?php echo rand(); ?>"></script>
