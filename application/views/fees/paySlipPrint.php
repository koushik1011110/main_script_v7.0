<style type="text/css">
@media print {
	.invoice-summary ul.amounts li {
		padding: 1px !important;
		border-bottom: #444444 1px solid;
	}
	.invoice table.table > tbody tr > td, 
	.invoice table.table > thead tr > th {
	    border-color: #444444 !important;
	    border-width: 1px !important;
	}
}
</style>
<?php
$record_array    = json_decode($record);
$currency_symbol = $global_config['currency_symbol'];
$basic           = $this->fees_model->getInvoiceBasic($studentID);
?>
<div class="row">
<?php 
$copy_names = array('Student Copy', 'Office Copy');
for ($i = 0; $i < count($copy_names); $i++) { 
?>
	<div class="col-xs-6">
		<div class="invoice">
			<h4 class='text-center mb-none' style="font-weight: bold;"><?php echo $copy_names[$i]; ?></h4>
			
			<div class="bill-info">
				<div class="row">
					<div class="col-xs-12">
						<div class="bill-data">
							<div class="text-center mt-xs">
								<img src="<?=$this->application_model->getBranchImage($basic['branch_id'], 'printing-logo')?>" style="max-height: 55px;" alt="Logo" />
							</div>
							<address style="text-align: center; margin-top: 5px;">
								<?php
								echo '<strong style="font-size: 16px; display: block; margin-bottom: 2px;">' . $basic['school_name'] . '</strong>';
								echo $basic['school_address'] . '<br/>';
								echo $basic['school_mobileno'] . '<br/>';
								echo $basic['school_email'] . '<br/>';
								?>
							</address>
							<div class="row">
								<div class="invoice-summary text-left mt-xs">
									<ul class="amounts">
										<li><?php echo '<strong>' . translate('date') . ' :</strong> ' . _d(date('Y-m-d')); ?></li>
										<li><?php echo '<strong>' . translate('student_name') . ' :</strong> ' . $basic['first_name'] . ' ' . $basic['last_name'] ?></li>
										<li><?php echo '<strong>' . translate('register_no') . ' :</strong> ' . $basic['register_no'] ?></li>
										<li><?php echo '<strong>' . translate('class') . ' :</strong> ' . $basic['class_name'] . ' (' . $basic['section_name'] . ')'; ?></li>
										<li><?php echo '<strong>' . translate('father_name') . ' :</strong> ' . $basic['father_name'] ?></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="table-responsive br-none">
				<table class="table invoice-items table-hover mb-none">
					<thead>
						<tr class="text-dark">
							<th id="cell-id" class="text-weight-semibold">#</th>
							<th id="cell-item" class="text-weight-semibold"><?= translate('fees_type') ?></th>
							<th id="cell-price" class="text-weight-semibold"><?= translate('amount') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count          = 1;
						$total_fine     = 0;
						$total_discount = 0;
						$total_paid     = 0;
						$total_balance  = 0;
						$total_amount   = 0;
						$payment_ids = array_filter(array_column($record_array, 'payment_id'));
						if (!empty($payment_ids)) {
							$this->db->select('*');
							$this->db->where_in('id', $payment_ids);
							$this->db->from('fee_payment_history');
							$paymentHistory = $this->db->get()->result();
						} else {
							$paymentHistory = array();
							foreach ($record_array as $rec) {
								$this->db->select('*');
								$this->db->from('fee_payment_history');
								if (isset($rec->feeType) && $rec->feeType == 'transport' && !empty($rec->trans_fd_id)) {
									$this->db->where('transport_fee_details_id', $rec->trans_fd_id);
								} elseif (!empty($rec->allocationID) && !empty($rec->feeTypeID)) {
									$this->db->where('allocation_id', $rec->allocationID);
									$this->db->where('type_id', $rec->feeTypeID);
								} else {
									continue;
								}
								$res = $this->db->get()->result();
								if (!empty($res)) {
									$paymentHistory = array_merge($paymentHistory, $res);
								}
							}
						}
						foreach ($paymentHistory as $key => $row) {
							$paid            = $row->amount;
							$discount        = $row->discount;
							$fine            = $row->fine;
							$total_paid     += $paid;
							$total_discount += $discount;
							$total_fine     += $fine;
							?>
						<tr>
							<td><?php echo $count++; ?></td>
							<td class="text-weight-semibold text-dark"><?php
							if (empty($row->transport_fee_details_id)) {
								echo get_type_name_by_id('fees_type', $row->type_id);
							} else {
								$month = get_type_name_by_id('transport_fee_details', $row->transport_fee_details_id, 'month');
								$month = $this->app_lib->getMonthslist($month);
								echo translate('transport_fees') . " - $month";
							}
							?></td>
							<td><?php echo currencyFormat($paid); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<div class="invoice-summary text-right mt-lg">
				<div class="row">
					<div class="col-lg-8">
						<ul class="amounts">
							<li><strong><?= translate('sub_total') ?> :</strong> <?= currencyFormat($total_paid + $total_discount); ?></li>
							<li><strong><?= translate('discount') ?> :</strong> <?= currencyFormat($total_discount); ?></li>
							<li><strong><?= translate('paid') ?> :</strong> <?= currencyFormat($total_paid); ?></li>
							<li><strong><?= translate('fine') ?> :</strong> <?= currencyFormat($total_fine); ?></li>
							<li>
								<strong><?= translate('total_paid') ?> (<?= translate('with_fine') ?>) : </strong> 
								<?php
								$grand_paid = currencyFormat($total_paid + $total_fine);
								echo $grand_paid;
								?>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="text-center mt-md">Generated at <?php echo _d(date('Y-m-d')) . ', ' . date('h:i A'); ?> | Powered by KKWEBMART</div>
		</div>
	</div>
<?php } ?>
</div>
