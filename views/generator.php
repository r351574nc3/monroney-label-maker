<?php 
$logo_handler = get_user_upload_handler('dealershipLogo'); 
$logo = $logo_handler->get_form_fields(
	array("Choose File", "Upload Your Logo"), 
	"tag-button red-text", 
	array('choose-file', 'upload-logo'));

$label_handler = get_user_upload_handler('customLabel'); 
$label = $label_handler->get_form_fields(
	array("Choose Image", "Upload Image"), 					//button text
	"tag-button red-text", 									//classes
	array('choose-label', 'upload-label'),					//indices
	array('gallery' => 'tag-gallery')						//data
	);

$debug = false;
$default = array(
	'stock'=>1234556,
	'vin'=>1234567890123456,
	'make'=>'Fast',
	'model'=>'Car',
	'year'=>'2014',
	'trim'=>'Sport'
);

//'<li><input class="tag-checkbox" type="checkbox" /><span>'.$option_name.'</span><div class="option-price float-right"><span class="dollar-sign">&#36;</span><input class="tag-input" type="text"/></div></li>';

?>

<script type="text/x-handlebars-template" id="dialogTemplate">
	<form id="{{id}}" class="{{class}}">
		{{#list fields}}{{/list}}
		<button class="dialogButton {{submitClass}}" id="{{submitId}}">{{submitText}}</button>
	</form>
</script>                        

<div id="tag-generator">
	<div id="generator-spinner-overlay"></div>
	<img id="generator-page-loader" src="<?php echo plugins_url('label-maker/js/modal/loader.gif'); ?>">          

    <form id="tag-preview">
        <h2 class="tag-h2">Label Preview</h2>

        <div id="tag-preview-window">
            <div id="tag-preview-header" class="tag-preview-section white-background">
                <div id="logoWrap">
                    <img id="dealershipLogo" class="invisible" />
                    <div id="dealershipText">
                        <h3 class="preview-h3 align-center" id="dealershipName">Dealership Name</h3>
                        <h4 class="preview-h4 align-center" id="dealershipTagline">Dealership Tagline</h4>
                     </div>
                </div>

                <ul id="vitalStats" class="white-background">
                    <li id="stockNoLabel" class="preview-label basal-font">Stock No.:<span class="float-right preview-span" id="stockNo"><?php echo ($debug) ? $default['stock'] : ''; ?></span></li>
                    <li id="vinLabel" class="preview-label basal-font">VIN:<span class="float-right preview-span basal-font" id="vin"><?php echo ($debug) ? $default['vin'] : ''; ?></span></li>
                    <li id="makeLabel" class="preview-label basal-font">Make:<span class="float-right preview-span basal-font" id="make"><?php echo ($debug) ? $default['make'] : ''; ?></span></li>
                    <li id="modelLabel" class="preview-label basal-font">Model:<span class="float-right preview-span basal-font" id="model"><?php echo ($debug) ? $default['model'] : ''; ?></span></li>
                    <li id="yearLabel" class="preview-label basal-font">Year:<span class="float-right preview-span basal-font" id="year"><?php echo ($debug) ? $default['year'] : ''; ?></span></li>
                    <li id="trimLabel" class="preview-label basal-font">Trim:<span class="float-right preview-span basal-font" id="trim"><?php echo ($debug) ? $default['trim'] : ''; ?></span></li>
                    <li id="msrpLabel" class="preview-label basal-font">MSRP:<span class="float-right preview-span basal-font" id="msrp">$0.00</span></li>
                </ul>
            </div>
            <div id="tag-preview-vitals" class="tag-preview-section white-background">
                <input id="tag-preview-vitals-border-top" class="preview-section-title white-text align-center basal-font" name="title_1" value="Dealer Added Equipment & Services" />
                <!-- <input class="preview-section-title color-white align-center" name="title_0" value="Consumer Protection Label" /> -->

            <!-- </div> -->
            <!-- <div id="tag-preview-info" class="tag-preview-section white-background"> -->
                
                <ul id="addendumOptions" class="white-background">
                    <li id="addendumOptionsExteriorList">
                        <input id="addendumOptionsExteriorHead" class="list-head basal-font" value="Exterior Options">
                        <ul id="exteriorOptions" class="indent-1">
                            <?php if ($debug) { ?>
                                <li class="option font-arial px-10" id="option_Wings">
                                    <span id="option_Wings_name" class="basal-font">Wings</span>
                                    <span class="float-right basal-font" id="option_Wings_value">+ &#36;0.00</span>
                                </li>                                
                            <? } ?>		
                        </ul>
                    </li>
                    
                    <li id="addendumOptionsInteriorList">
                        <input id="addendumOptionsInteriorHead" class="list-head" value="Interior Options">
                        <ul id="interiorOptions" class="indent-1">
                        <?php if ($debug) { ?>
                            <li class="option font-arial px-10" id="option_Full_Bar">
                                <span id="option_Full_Bar_name" class="basal-font">Full Bar</span>
                                <span class="float-right basal-font" id="option_Full_Bar_value">+ &#36;0.00</span>
                            </li>
                         <? } ?>		
                        </ul>
                    </li>

                    <!--
                    <li id="addendumDiscountsItems">
                        <input id="addendumOptionsDiscountsHead" class="list-head" value="Discounts and Specials">
                        <ul id="discounts" class="indent-1">
                            <?php //if ($debug) { ?>
                            <li class="discount font-arial px-10" id="discount_Five_Finger">
                                <span id="discount_Five_Finger_name">Five Finger</span>
                                <span class="float-right" id="discount_Five_Finger_value">- &#36;5.00</span>
                            </li>
                            <?php // } ?>
                        </ul>
                    </li>
                    -->
                </ul>

                <fieldset id="total-block" class="total-block">
                    <label id="total-label" class="list-head total-label basal-font" for="total">Total</label>
                    <input id="total" name="total" class="total-field align-right basal-font">
                </fieldset>
            </div>

            <div id="tag-preview-footer" class="tag-preview-section white-background">
                <input id="tag-preview-footer-border-top" class="preview-section-title white-text align-center basal-font" name="title_2" value="Consult Free Gas Mileage Guide" />
                <img id="customLabel" src="<?php echo ($debug) ? 	'http://www.taglinemediagroup.com/monroney/wp-content/uploads/label-generator/customLabel/fuel_label.jpg' : ''; ?>" />
            </div>
        </div>
    </form>


    <div id="tag-options">
        <h2 class="tag-h2 float-left">Label Options</h2>
                        <ul class="float-right login-links">
                           <li id="login-label" class="float-left">
                                <button class="icon-button black-text"><span class="icon-key2"></span> Log In</button>        
                            </li>
                            <li id="signup-label" class="float-left">
                                <button class="icon-button black-text"><span class="icon-pencil"></span> Sign Up</button>        
                            </li>
                        </ul>
        <div class="tag-tabs clear">
            <div class="tag-tab-holder active" id="tag-tab-holder-0">
                <div class="tag-tab"></div>
                <span class="tag-tab-text">Branding Options</span>
            </div>
            <div class="tag-tab-holder inactive" id="tag-tab-holder-1">
                <div class="tag-tab"></div>
                <span class="tag-tab-text">Vehicle info</span>
            </div>
            <div class="tag-tab-holder inactive" id="tag-tab-holder-2">
                <div class="tag-tab"></div>
                <span class="tag-tab-text">Addendum Options</span>
            </div>
            <!-- <div class="tag-tab-holder inactive" id="tag-tab-holder-3">
                <div class="tag-tab"></div>
                <span class="tag-tab-text">Deals and Specials</span>
            </div> -->
        </div>
        <form class="tag-frames" enctype="multipart/form-data" action="" method="POST">
            <?php wp_nonce_field('process_user_upload', '_file_upload_handler', true, true); ?>
            <div class="tag-frame visible" id="tag-frame-0" name="branding_options">
                <div class="tag-row row-1 divider divider-bottom">
                    <div class="tag-col divider divider-right first-col col-1">
                        <h4 class="tag-h4">Label Color</h4>
                        <ul class="tag-h-ul">
                            <li class="colorbox-wrap"><div class="colorbox blue-background" id="#23498a"></div></li>
                            <li class="colorbox-wrap selected"><div class="colorbox green-background" id="#24a649"></div></li>
                            <li class="colorbox-wrap"><div class="colorbox red-background" id="#bf2026"></div></li>
                            <li class="colorbox-wrap"><div class="colorbox gray-background" id="#929491"></div></li>
                            <li class="colorbox-wrap"><div class="colorbox black-background" id="#000000"></div></li>
                        </ul>
                    </div>
                    <div class="tag-col col-2">
                        <h4 class="tag-h4">PDF Controls</h4>
				        <ul class="tag-nav-buttons" id="pdfControls">
				            <li id="inspect-label" class="inline-block-li">
				                <button class="icon-button black-text"><span class="icon-screen"></span></button>        
								<div class="tooltip">Preview</div>
				            </li>
				            <li id="save-label" class="inline-block-li">
				                <button class="icon-button black-text"><span class="icon-upload2"></span></button>        
								<div class="tooltip">Save</div>
				            </li>
				            <li id="load-label" class="inline-block-li">
				                <button class="icon-button black-text"><span class="icon-download2"></span></button>        
								<div class="tooltip">Load</div>
				            </li>
				            <li id="print-label" class="inline-block-li">
				                <button class="icon-button black-text"><span class="icon-print"></span></button>        
								<div class="tooltip">Print</div>
				            </li>
				            <li id="reset-label" class="inline-block-li">
				                <button class="icon-button black-text"><span class="icon-remove"></span></button>        
								<div class="tooltip">Reset</div>
				            </li>
				        </ul>
                    </div>
                    <!-- <div class="tag-col col-2">
                        <h4 class="tag-h4">Font Type</h4>
                        <ul class="tag-v-ul">
                            <li>
                                <input type="radio" class="tag-input" name="fontFamily" value="sans-serif" selected />
                                <span class="font-sans-serif">Sans Serif</span>
                            </li>
                            <li>
                                <input type="radio" class="tag-input" name="fontFamily" value="serif" />
                                <span class="font-serif">Serif</span>
                            </li>
                            <li>
                                <input type="radio" class="tag-input" name="fontFamily" value="monospace" />
                                <span class="font-monospace">Monospace</span>
                            </li>
                        </ul>
                    </div>
                    <div class="tag-col col-3">
                        <h4 class="tag-h4">Font Style</h4>
                        <ul class="tag-v-ul">
                            <li>
                                <input type="checkbox" class="tag-input font-sans-serif" name="fontWeight" value="bold" />
                                <span class="bf font-sans-serif">Bold</span>
                            </li>
                            <li>
                                <input type="checkbox" class="tag-input font-sans-serif" name="fontStyle" value="italic" />
                                <span class="ital font-sans-serif">Italic</span>
                            </li>
                        </ul>
                    </div> -->	
                </div>
                <div class="tag-row divider divider-bottom row-2 full-width">
                    <div class="tag-col col-1 half-width">
                        <h4 class="tag-h4">Custom Text Branding</h4>
                        <ul class="tag-v-ul">
                            <li>
                                <input type="text" class="tag-input absolute" name="dealershipName" placeholder="[Dealership Name]" />
                            </li>
                            <li>
                                <input type="text" class="tag-input absolute" name="dealershipTagline" placeholder="[Tagline]" />
                            </li>
                        </ul>
                    </div>
                    <div class="tag-col col-2 half-width">
                        <h4 class="tag-h4">Logo Branding</h4>
                        <?php echo $logo; ?>
                        <button class="tag-button" name="toggleVisibility" />Hide Logo</button>
                    </div>
                </div>
                <div class="tag-row row-3">
                    <div class="tag-col col-1">
                        <h4 class="tag-h4">Label Button</h4>
                        <p class="tag-headnote">Use your own image:</p>
                        <?php echo $label; ?>
                        <label for="labelCaption" class="tag-label new-line">Caption (If Any)</label>
                        <textarea name="labelCaption" class="tag-input"></textarea>
                    </div>
                    <div class="tag-col col-2">
                        <div class="tag-gallery">
                        </div>	
                    </div>
                </div>
            
            </div>
            <div class="tag-frame invisible" id="tag-frame-1" name="vehicle_info">
                <div class="tag-row row-1">
                    <div class="tag-col col-1">
                        <h4 class="tag-h4">Vehicle Information</h4>
                        <fieldset name="vehicleConfig">
                            <ul class="tag-v-ul">
                                <li id="vehicleMakeConfig">
                                    <label class="tag-label run-in" for="vehicleMake">Make</label>
                                    <!-- <button class="destroy-button run-in">&ndash;</button> -->
                                    <select class="tag-select" name="make">
                                        <option value='select_all' selected>[Select Make]</option>
                                        <option class="green-text" value='add_new'>[Add New]</option>
                                    </select>
                                    <input name="make" data-type="make" data-id="" type="text" class="absolute-right config-input run-in tag-input" placeholder="[make]">
                                    <button class="add-button absolute-right run-in">+</button>
                                </li>
                                <li id="vehicleModelConfig">
                                    <label class="tag-label run-in" for="vehicleModel">Model</label>
                                    <!-- <button class="destroy-button run-in">&ndash;</button> -->

                                    <select class="tag-select" name="model">
                                        <option value='select_all' selected>[Select Model]</option>
                                        <option class="green-text" value='add_new'>[Add New]</option>
                                    </select>
                                    <input name="model" data-type="model" data-id="" type="text" class="absolute-right config-input run-in tag-input" placeholder="[model]">
                                    <button class="add-button absolute-right run-in">+</button>

                                </li>
                                <li id="vehicleYearConfig">
                                    <label class="tag-label run-in" for="vehicleYear">Year</label>
                                    <!-- <button class="destroy-button run-in">&ndash;</button> -->
                                    <select class="tag-select" name="year">
                                        <option value='select_all' selected>[Select Year]</option>
                                        <option class="green-text" value='add_new'>[Add New]</option>
                                    </select>
                                    <input name="year" type="text" data-type="year" data-id="" class="absolute-right config-input run-in tag-input" placeholder="[year]">
                                    <button class="add-button absolute-right run-in">+</button>

                                </li>
                                <li class="vehicle-numbers">
                                    <label class="tag-label width-short" for="vehicleTrime">Stock Number</label>
                                    <input class="tag-input float-right" type="text" name="stockNo" />
                                </li>
                                <li class="vehicle-numbers">
                                    <label class="tag-label width-short" for="vehicleTrime">Trim</label>
                                    <input class="tag-input float-right" type="text" name="trim" />
                                </li>
                                <li class="vehicle-numbers">
                                    <label class="tag-label width-short" for="vehicleVIN">VIN</label>
                                    <input class="tag-input float-right" maxlength="16" type="text" name="vin" />
                                </li>
                                <li class="vehicle-numbers">
                                    <label class="tag-label" for="vehicle-msrp">MSRP</label>
                                    <input class="tag-input float-right" type="text" name="msrp" />                                
                                </li>
                            </ul>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="tag-frame invisible" id="tag-frame-2" name="addendum_options">
                <div class="tag-row row-1">
                    <div class="tag-col col-1">
                        <h4 class="tag-h4 block-label">Exterior Options</h4>
                     </div>
                     <div class="tag-col col-2">
                     	<div class="block-list-container">
                            <ul id="exterior-options" class="block-list">
                            </ul>
                            <div class="add-new-option block-list-add-container">
                                <div class="invisible block-list-new-item" id="exterior-input-container">
                                    <input type="text" placeholder="" id="exterior-input" class="tag-input option-input" />
                                    <button class="add-button option-button float-right" id="exterior-add-button">+</button>
                                	<input type="text" placeholder="0.00" id="exterior-price-input" class="tag-input price-input" />
                                </div>
                                <div class="hover-blue block-list-add-label" id="add-new-exterior-item">Add New Option</div>
                            </div>
						</div>                     
                    </div>
                </div>

                <div class="tag-row row-2">
                    <div class="tag-col col-1">
                        <h4 class="tag-h4 block-label">Interior Options</h4>
                     </div>
                     <div class="tag-col col-2">
                     	<div class="block-list-container">
                            <ul id="interior-options" class="block-list">
                            </ul>
                            <div class="add-new-option block-list-add-container">
                                <div class="invisible block-list-new-item" id="interior-input-container">
                                    <input type="text" placeholder="" id="interior-input" class="tag-input option-input" />
                                    <button class="add-button option-button float-right" id="interior-add-button">+</button>
                                    <input type="text" placeholder="0.00" id="interior-price-input" class="tag-input price-input" />
                                </div>
                                <div class="hover-blue block-list-add-label" id="add-new-interior-item">Add New Option</div>
                            </div>
						</div>
                     </div>
                </div>

            </div>
            <div class="tag-frame invisible" id="tag-frame-3" name="discounts_and_deals">
                
                <div class="tag-row row-1">
                    <div class="tag-col col-1">
                        <h4 class="tag-h4">Select a Discount</h4>
                     </div>
                     <div class="tag-col col-2">
                        <ul class= "block-list" id="discountList">
                                                        
                        </ul>
                     </div>
                </div>
                <div class="tag-row row-2">
                    <div class="tag-col col-1">
                        <h4 class="tag-h4">Add New Discount</h4>
                     </div>
                    <div class="tag-col col-2">
                        <ul class="list-vertical">
                            <li>
                                <label class="tag-label width-short" for="discountPrice">Amount</label>
                                <input class="tag-input float-right" type="number" name="discountAmount" />
                            </li>
                            <li>
                                <label class="tag-label width-short" for="discountType">Type</label>
                                <select name="discountType" class="float-right tag-select">
                                    <option name="percentage">Percentage</option>
                                    <option name="value">Value</option>
                                </select>
                            </li>
                            <li>
                                <label class="tag-label width-short" for="discount">Discount</label>
                                <input class="tag-input float-right" type="text" name="discount" />
                            </li>
                            <li>
                                <button class="add-button discount-button float-right" id="discount-add-button">+</button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>
    </div>

    </div>
</div>