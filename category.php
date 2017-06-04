<?php include '_inc/header.php';
	
	//array to hold items 
	$itemArray = array();
	//for items in online stock
	if(!empty($_GET['catid']) && empty($_GET['stock'])){
		
		//value to hold category id && dept id
		$catId  = $_GET['catid'];

		$depart = $_GET['dept'];

		//get gender value and gender name
		if (!empty($_GET['deptmen'])){
			$gender      = 3;
			$gender_name = 'men';
		}else if(!empty($_GET['deptwomen'])){
			$gender      = 6;
			$gender_name = 'women';
		}
		

		$qu = "SELECT warehouses.item_id, sum(warehouses.qty) as qty, warehouses.size_id, items.gender_id, items_dept.desc, items_dept.long_desc, items_dept.dept_id, items.msrp,items.rtp
				FROM `warehouses`
				JOIN `items`      ON items.item_id      = warehouses.item_id
				JOIN `items_dept` ON items_dept.dept_id = items.dept_id
				WHERE items.gender_id  IN (:gender, 5)
				AND warehouses.wrhs_id IN ($locStr)";
		if($catId == 65){
			$qu .= " AND items.dept_id IN (65,27,4,7)";
		}else if ($catId == 27){
			$qu .= " AND items.dept_id IN (27,4)";
		}else if ($catId == 5){
			$qu .= " AND items.dept_id IN (5,9)";
		}else if ($catId == 60){
			$qu .= " AND items.dept_id IN (60,68)";
		}else if ($catId == 21){
			$qu .= " AND items.dept_id IN (21,15)";
		}else if ($catId == 54){
			$qu .= " AND items.dept_id IN (54,50,46)";
		}else if ($catId == 55){
			$qu .= " AND items.dept_id IN (55,47)";
		}else{
			$qu .= " AND items.dept_id = $catId";
		}
			$qu	.= " AND warehouses.size_id NOT IN (0, 1)
				     GROUP BY warehouses.item_id, warehouses.size_id
                     ORDER BY warehouses.item_id DESC";
		$db1->query($qu);
		$db1->bind(":gender", $gender);
		$products = $db1->fetchAll();

	//for items in all stocks
	}else if(!empty($_GET['catid']) && !empty($_GET['stock'])){

		//value to hold category id
		$catId = $_GET['catid'];

		//get gender value and gender name
		if (!empty($_GET['deptmen'])){
			$gender = 3;
			$gender_name = 'men';
		}else if(!empty($_GET['deptwomen'])){
			$gender = 6;
			$gender_name = 'women';
		}
	
		$qu = "SELECT warehouses.item_id, warehouses.qty, warehouses.size_id, items.gender_id, items_dept.desc, items_dept.long_desc, items_dept.dept_id, items.msrp,items.rtp
				FROM `warehouses`
				JOIN `items`      ON items.item_id      = warehouses.item_id
				JOIN `items_dept` ON items_dept.dept_id = items.dept_id
				WHERE items.gender_id IN (:gender, 5)
				AND warehouses.wrhs_id NOT IN (62)";
		if($catId == 65){
			$qu .= " AND items.dept_id IN (65,27,4,7)";
		}else if ($catId == 27){
			$qu .= " AND items.dept_id IN (27,4)";
		}else if ($catId == 5){
			$qu .= " AND items.dept_id IN (5,9)";
		}else if ($catId == 60){
			$qu .= " AND items.dept_id IN (60,68)";
		}else if ($catId == 21){
			$qu .= " AND items.dept_id IN (21,15)";
		}else if ($catId == 54){
			$qu .= " AND items.dept_id IN (54,50,46)";
		}else if ($catId == 55){
			$qu .= " AND items.dept_id IN (55,47)";
		}else{
			$qu .= " AND items.dept_id = $catId";
		}
			$qu .= " AND warehouses.size_id NOT IN (0, 1)
				     GROUP BY warehouses.item_id, warehouses.size_id
                     ORDER BY warehouses.item_id DESC";
		$db1->query($qu);
		$db1->bind(":gender", $gender);
		$products = $db1->fetchAll();
	}else{
		$catId  = "";
	}

	//for searching for items
	if(!empty($_GET['txtSearch'])){

		//search for specific item
		$txtSearch = $_GET['txtSearch'];
		$db1->query("SELECT warehouses.item_id, sum(warehouses.qty) as qty, warehouses.size_id, items.gender_id, items_dept.desc, items_dept.long_desc,items_dept.dept_id,items.msrp ,items.rtp
					  FROM `warehouses`
					  JOIN `items`      ON items.item_id      = warehouses.item_id
					  JOIN `items_dept` ON items_dept.dept_id = items.dept_id
					  WHERE warehouses.wrhs_id IN ($locStr)
					  AND warehouses.size_id NOT IN (0, 1) 
					  AND (items.item_id  LIKE CONCAT('%', :txtSearch, '%') OR items_dept.long_desc LIKE CONCAT('%', :txtSearch, '%'))
				      GROUP BY warehouses.item_id, warehouses.size_id
                      ORDER BY warehouses.item_id DESC");
		$db1->bind(":txtSearch", $txtSearch);
		$products = $db1->fetchAll();
		if(!empty($products)){
			foreach($products as $pros){
				$catId  = $pros['dept_id'];
				$depart = $pros['long_desc'];
			}
		}
		//get the gender of this specific item
		$db1->query("SELECT gender_id FROM `items` WHERE item_id = :id");
		$db1->bind(":id", $txtSearch);
		$genders = $db1->fetchAll();
		if(!empty($genders)){
			foreach($genders as $gend){
				if($gend['gender_id'] == 3){
					$gender_name = 'men';
					$gender      = $gend['gender_id'];
				}else{
					$gender_name = 'women';
					$gender      = $gend['gender_id'];
				}
			}
		}
		
	}else if(!empty($_GET['selGnd']) && $_GET['txtSearch'] == ''){
		$query = "SELECT warehouses.item_id, sum(warehouses.qty) as qty, warehouses.size_id, items.gender_id, items_dept.desc, items_dept.long_desc,items_dept.dept_id,items.msrp,items.rtp
				  FROM `warehouses`
				  JOIN `items`      ON items.item_id      = warehouses.item_id
				  JOIN `items_dept` ON items_dept.dept_id = items.dept_id
                  WHERE warehouses.wrhs_id IN ($locStr)";
		if (!empty($_GET['selGnd'])) {
			$gender   = $_GET['selGnd'];
			$query   .= " AND items.gender_id = '{$gender}'";
		}

		if (!empty($_GET['selCat'])) {
			$category = $_GET['selCat'];
			$query   .= " AND items.dept_id = '{$category}'";
		}

		if(!empty($_GET['selSiz'])){
			$sizee  = $_GET['selSiz'];
			$query .= " AND warehouses.size_id = '{$sizee}'";
		}

		$query .= " GROUP BY warehouses.item_id, warehouses.size_id
                  	ORDER BY warehouses.item_id DESC";

		$sq = $db1->query($query);
		$db1->bind(":txtSearch", $txtSearch);
		$products = $db1->fetchAll();

		if(!empty($products)){
			foreach($products as $pros){
				$catId  = $pros['dept_id'];
				$depart = $pros['long_desc'];
			}
		}

		if (!empty($_GET['selGnd'])){
			$gender_id = $_GET['selGnd'];
			if($gender_id == 3){
				$gender_name = 'men';
			}else{
				$gender_name = 'women';
			}
		}
	}
?>

<div class="container">

	<div class="row">

		<div>

			<!-- <div class="search"> -->
				<!-- <div class="titleHeader clearfix">
					
				</div> -->

				<div class="search_div" id="sticker">
					<table style="margin: 0 auto;">
						<tr>
							<td class="cat_head">
								<div class="items_search">
									<form name="frmSearch" method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-inline">
									  	<input name="txtSearch" id="txtSearch" type="text" class="spans3" placeholder="<?Php echo $lang['TYPE_SEARCH_TERM'] ?>...">
										<select name="selGnd" id="selGnd" class="spans3" style="display:none;">
									   	  <option value="<?php echo $gender ?>"><?php echo $gender_name ?></option>
									    </select>
									    <select name="selCat" id="selCat" class="spans3" style="display:none;">
									  	  <option value="<?php echo $catId ?>"><?php echo $depart ?></option>
									    </select>
									    <select name="selSiz" id="selSiz" class="spans3">
									    <?php
									    //query to select items available in warehouses && in cairo stores
										$db1->query("SELECT DISTINCT(warehouses.size_id), items_size.desc
											  FROM `warehouses`
											  JOIN `items_size` ON items_size.size_id = warehouses.size_id
											  JOIN `items`      ON items.item_id      = warehouses.item_id
											  WHERE items.gender_id = :gender
											  AND items.dept_id = :catId
									 		  AND warehouses.wrhs_id IN ($locStr)
											  AND items_size.size_id NOT IN (0 , 1)
											  AND warehouses.qty > 2
											  GROUP BY warehouses.size_id
											  ORDER BY warehouses.size_id");
										$db1->bind(":gender", $gender);
										$db1->bind(":catId", $catId);
										$result = $db1->fetchAll(); 
										if(!empty($result)){
											echo '<option value="">--'.$lang['SELECT_SIZE'].'--</option>';
											foreach($result as $item){
												echo '<option value="'.$item['size_id'].'">'.$item['desc'].'</option>';
											}
										}
									    ?>
									    </select>
									    <input type="hidden" id="catid" value="<?php echo $_GET['catid'] ?>">
									    <select name="selStock" id="selStock" class="spans3">
									  	  <option value="0">--<?php echo $lang['SELECT_STOCK'] ?>--</option>
									  	  <option value="1">--<?php echo $lang['ONLINE_STOCK'] ?>--</option>
									  	  <option value="2">--<?php echo $lang['STORES_STOCK'] ?>--</option>
									    </select>
									    <button type="submit" class="btn btn-primary"><i class="icon-search"></i></button>
									</form><!--end form-->
								</div>	
							</td>
							<td class="cat_head">
								<div class="pro-range-slider">
									<div class="price-range">
										<p class="clearfix">
										  <label>-- <?php echo $lang['RANGE_YOUR_PRICE'] ?>:</label>
										  <input type="text" id="amount">
										</p>
										<div id="slider-range"></div>
										<br>
									</div>
								</div>	
							</td>
						</tr>
					</table>
				</div>

			<!-- </div>end search -->
			<?php
				if(!empty($_GET['deptmen'])){
					echo '<input type="hidden" id="gend_id" value="3">';
				}else if(!empty($_GET['deptwomen'])){
					echo '<input type="hidden" id="gend_id" value="6">';
				}
			?>
			<div class="row">
				<ul class="hProductItems clearfix">
					<?php 
					if(!empty($products)){
						$checkValue = 0;

						foreach ($products as $key => $value):
							$done = 0;
							//check for hold items
							if (in_array($holdItems[$value['item_id'].$value['size_id']], $holdItems)){
								if($value["qty"] - $holdItems[$value['item_id'].$value['size_id']]["qty"] > 2){
									$done = 1;
								}else{
									$done = 0;
								}
							}else if ($value['qty'] > 2){
								$done = 1;
							}

							if($done == 1){

								if(!in_array($value['item_id'], $itemArray)){

									if(!empty($_SESSION['rvnFiles'])){
										$files = $_SESSION['rvnFiles'];
									}else{
										$dir    = '/home/tameras/public_html/collection/large';
										$files  = scandir($dir);
										$_SESSION['rvnFiles'] = $files;
									}
								
									$url = $value['item_id'].'a.jpg';
									if(in_array($url, $files)){
										$checkValue++;
						    ?>
									    <li class="span3 clearfix">
											<div class="thumbnail">
												<?php
												if($value['gender_id'] == 3){
													$gender_name = 'men';
												}else{
													$gender_name = 'women';
												}
												$dept_name = preg_replace('/\s+/', '-', $lang[$value['desc']]);
												$deptname  = strtolower($dept_name);
													if(!empty($_GET['stock'])){
														echo '<a href="products/'.$gender_name.'/'.$deptname.'/'.$value['item_id'].'"><img class="lazy" data-src="'.$small_link.''.$value['item_id'].'a.jpg?x=1" src="img/loading.gif" alt=""></a>';
													}else{
														echo '<a href="product/'.$gender_name.'/'.$deptname.'/'.$value['item_id'].'"><img class="lazy" data-src="'.$small_link.''.$value['item_id'].'a.jpg?x=1" src="img/loading.gif" alt=""></a>';
													}
												?>
											</div>
											<div class="thumbSetting">
												<div class="yasen">
													<?php
													if(!empty($_GET['stock'])){
														$db1->query("SELECT warehouses.item_id , warehouses.qty
																	  FROM `warehouses`
																	  JOIN `items`      ON items.item_id = warehouses.item_id
																	  JOIN `items_dept` ON items_dept.dept_id = items.dept_id
																	  WHERE items.gender_id IN ( 3, 5, 6 )
																	  AND warehouses.wrhs_id IN ($locStr)
																      AND warehouses.size_id NOT IN (0 , 1)
																      AND warehouses.item_id = :id
																      AND warehouses.qty > 2
															          GROUP BY warehouses.item_id
															          ORDER BY warehouses.item_id, warehouses.size_id ASC");
														$db1->bind(":id", $value['item_id']);
														$results = $db1->fetchAll(); 
														if(!empty($results)){
															foreach($results as $ress){
																echo '<div class="available_online">'.$lang['AVAILABLE_ONLINE'].'</div>';
															}
														}else{
															echo '<div class="available_store">'.$lang['RESERVE_IN_STORE'].'</div>';
														}
													}else{
													?>
														<div class="available_online"><?php echo $lang['AVAILABLE_ONLINE'] ?></div>
													<?php
													}
													?>
												</div>
												<div class="product-desc">
													<p><?php echo $lang[$value['desc']] ?></p>
												</div>
												<div class="thumbTitle"> 
													<h3>
													<span class="invarseColor" style="font-family: Arial, Tahoma sans-serif;"><?php echo $value['item_id']; ?><?php echo $value['wrhs_id']; ?></span>
													<?php if($value['msrp'] != $value['rtp']): ?>
														<span class="label label-info"><?php echo $lang['SALE'] ?></span>
													<?php endif; ?>
													</h3>
												</div>
												<div class="thumbPrice">
													<input type="hidden" id="flag" value="<?php echo $value['gender_id'] ?>">
													<?php if($value['msrp'] != $value['rtp']): ?>
													<span><span class="strike-through"><?php echo $value['msrp'] ?></span><span class="after_sale"><?php echo $value['rtp'] ?></span></span>
													<?php else: ?>
													<span class="noSale"><?php echo $value['msrp'] ?></span>
													<?php endif; ?>
												</div>

												<div class="thumbButtons">
													<?php
													if(!empty($_GET['stock'])){
														echo '<button rel="'.$value['item_id'].'" class="quickViews btn btn-primary btn-small btn-block">
																'.$lang['QUICK_VIEW'].'
																</button>';
													}else{
														echo '<button rel="'.$value['item_id'].'" class="quickView btn btn-primary btn-small btn-block">
																'.$lang['QUICK_VIEW'].'
																</button>';
													}
													?>
												</div>
											</div>
										</li>
									<?php
									array_push($itemArray, $value['item_id']);
									}  
								}
							}
 						endforeach;
 						if($checkValue == 0){
 					?>

 							<div class='alert alert-error'>
								<button type='button' class='close' data-dismiss='alert'>&times;</button>
								<h4><?php echo $lang['OH_WE_ARE_SO_SORRY'] ?></h4>
								<?php echo $lang['THERE_IS_NO_PRODUCT_THAT_MATCHES_THE_SEARCH_CRITERIA'] ?>.
							</div>
					<?php
 						}
 					}else{ 
 					?>
					<div class='alert alert-error'>
						<button type='button' class='close' data-dismiss='alert'>&times;</button>
						<h4><?php echo $lang['OH_WE_ARE_SO_SORRY'] ?></h4>
						<?php echo $lang['THERE_IS_NO_PRODUCT_THAT_MATCHES_THE_SEARCH_CRITERIA'] ?>.
					</div>
					<?php 
					} 
					?>
				</ul>
			</div><!--end row-->

		</div><!--end span9-->

	</div><!--end row-->

</div><!--end conatiner-->

<div style='display:none'>
	<div id="showItem"></div>
</div>

<?php include '_inc/footer.php' ?>