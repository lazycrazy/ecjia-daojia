<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia-merchant.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	var images_url = "{$images_url}";
	ecjia.merchant.preview.init();
</script>
<!-- {/block} -->

<!-- {block name="home-content"} -->
<div class="page-header">
	<div class="pull-left">
		<h2>
			<!-- {if $ur_here}{$ur_here}{/if} -->
		</h2>	
	</div>	
	<div class="pull-right">
		<!-- {if $action_link} -->
		<a class="btn btn-primary data-pjax" href="{$action_link.href}" id="sticky_a"><i class="fa fa-reply"></i> {$action_link.text} </a>
		<!-- {/if} -->	
		<!-- {if $action_linkedit} -->
		<a href="{$action_linkedit.href}" class="btn btn-primary data-pjax"><i class="fa fa-edit"></i> {$action_linkedit.text} </a>
		<!-- {/if} -->
	</div>	
	<div class="clearfix"></div>
</div>

<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-body">
			<div class="col-lg-12">
				<form class="form-inline pull-right" method="post" action="{url path='goods/merchant/preview'}" name="searchForm" data-id="{$goods.goods_id}">
					<div class="form-group">
						<input class="form-control" type="text" name="keywords" value="{$goods.goods_id}" placeholder="{lang key='goods::goods.id_or_sn'}"/>
					</div>
					<button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> {lang key='goods::goods.search'}</button>
				</form>
			</div>
			
			<div id="detail">
				<div class="tm-detail-meta tm-clear">
					<div class="tm-clear">
						<div class="tb-property">
							<div class="tb-wrap">
							  	<div class="tb-detail-hd">
							    	<h1 data-spm="1000983">{$goods.goods_name}</h1>
							    	<div class="tb-detail-sellpoint"></div>
							 	</div>
							  	<div class="tm-fcs-panel">
							    	<dl class="tm-tagPrice-panel">
							      		<dt class="tb-metatit">市场售价</dt>
							      		<dd>
							     			<em class="tm-yen">¥</em>
							    			<span class="tm-price">{$goods.market_price}</span>
							    		</dd>
							    	</dl>
							    	<dl class="{if $goods.is_promote_now}tm-tagPrice-panel{else}tm-promo-price{/if}">
								     	<dt class="tb-metatit">本店售价</dt>
								      	<dd>
								          	<em class="tm-yen">¥</em>
								          	<span class="tm-price">{$goods.shop_price}</span>&nbsp;&nbsp;
								      	</dd>
							    	</dl>
							    	
							    	{if $goods.is_promote_now}
							    	<dl class="tm-promo-panel tm-promo-cur">
								     	<dt class="tb-metatit">促销价格</dt>
								      	<dd>
								        	<div class="tm-promo-price">
								          		<em class="tm-yen">¥</em>
								          		<span class="tm-price">{$goods.promote_price}</span>&nbsp;&nbsp;
								          	</div>
								      	</dd>
							    	</dl>
							    	
							    	<dl class="tm-promo-panel tm-promo-cur">
								     	<dt class="tb-metatit">促销时间</dt>
								      	<dd>
								          	<span class="tm-price">{$goods.promote_start_time} 至 {$goods.promote_end_time}</span>
								      	</dd>
							    	</dl>
							    	{/if}
							  	</div>
							  	<div class="tb-key">
							    	<div class="tb-skin">
							      		<div class="tb-sku">
							      			<dl class="tb-amount tm-clear">
							          			<dt class="tb-metatit">商品货号</dt>
							          			<dd id="J_Amount">
										            <em id="J_EmStock" class="tb-hidden" style="display: inline;">{$goods.goods_sn}</em>
										            <span id="J_StockTips"></span>
							          			</dd>
							        		</dl>
							        		<dl class="tb-amount tm-clear">
							          			<dt class="tb-metatit">数量</dt>
							          			<dd id="J_Amount">
										            <em id="J_EmStock" class="tb-hidden" style="display: inline;">库存{$goods.goods_number}件</em>
										            <span id="J_StockTips"></span>
							          			</dd>
							        		</dl>
							        		<dl class="tb-amount tm-clear">
							          			<dt class="tb-metatit">警告数量</dt>
							          			<dd id="J_Amount">
							            			<em id="J_EmStock" class="tb-hidden" style="display: inline;">{$goods.warn_number}件</em>
							            			<span id="J_StockTips"></span>
							          			</dd>
							        		</dl>
											<dl class="tb-amount tm-clear">
											    <dt class="tb-metatit">商品重量</dt>
											    <dd id="J_Amount">
											        <em id="J_EmStock" class="tb-hidden" style="display: inline;">{$goods.goods_weight}</em>
											        <span id="J_StockTips"></span>
											    </dd>
											</dl>
											<dl class="tb-amount tm-clear">
											    <dt class="tb-metatit">商品分类</dt>
											    <dd id="J_Amount">
											        <em id="J_EmStock" class="tb-hidden" style="display: inline;">{$cat_name}</em>
											        <span id="J_StockTips"></span>
											    </dd>
											</dl>
											<dl class="tb-amount tm-clear">
											    <dt class="tb-metatit">店铺分类</dt>
											    <dd id="J_Amount">
											        <em id="J_EmStock" class="tb-hidden" style="display: inline;">{$merchant_cat_name}</em>
											        <span id="J_StockTips"></span>
											    </dd>
											</dl>
											<dl class="tb-amount tm-clear">
											    <dt class="tb-metatit">添加时间</dt>
											    <dd id="J_Amount">
											        <em id="J_EmStock" class="tb-hidden" style="display: inline;">{$goods.add_time}</em>
											        <span id="J_StockTips"></span>
											    </dd>
											</dl>
											<dl class="tb-amount tm-clear">
											    <dt class="tb-metatit">更新时间</dt>
											    <dd id="J_Amount">
											        <em id="J_EmStock" class="tb-hidden" style="display: inline;">{$goods.last_update}</em>
											        <span id="J_StockTips"></span>
											    </dd>
											</dl>
											
											<dl class="tb-amount tm-clear">
											    <dt class="tb-metatit">加入推荐</dt>
											    <dd id="J_Amount">
											    	{lang key='goods::goods.is_best'}
											        <em id="J_EmStock" class="tb-hidden m_r5" style="display: inline;">
											        	{if $goods.store_best}
											            <i class="fa fa-check"></i>{else}<i class="fa fa-times"></i>
											            {/if}
											        </em>
											        {lang key='goods::goods.is_new'}
											        <em id="J_EmStock" class="tb-hidden m_r5" style="display: inline;">
											        	{if $goods.store_new}
											            <i class="fa fa-check"></i>{else}<i class="fa fa-times"></i>
											            {/if}
											        </em>
											        {lang key='goods::goods.is_hot'}
											        <em id="J_EmStock" class="tb-hidden" style="display: inline;">
											        	{if $goods.store_hot}
											            <i class="fa fa-check"></i>{else}<i class="fa fa-times"></i>
											            {/if}
											        </em>
											    </dd>
											</dl>
										</div>
							    	</div>
							  	</div>
							</div>
						</div>
						<div class="tb-gallery">
							<div id="tbody">
								{if $goods_photo_list}
							    <div id="mainbody">
							    	<!-- {foreach from=$goods_photo_list key=k item=val} -->
							    	{if $k eq 0}
							      	<img src="{$val.img_url}" id="mainphoto" />
							      	{/if}
							      	<!-- {/foreach} -->
							    </div>
							    <img src="{$images_url}/goleft.gif" width="11" height="56" id="goleft" />
							    <img src="{$images_url}/goright.gif" width="11" height="56" id="goright" />
							    <div id="photos">
							    	<div id="showArea">
								        <!-- SRC: 缩略图地址 REL: 大图地址  NAME: 网址 -->
								        <!-- {foreach from=$goods_photo_list key=k item=val} -->
								        <img src="{$val.thumb_url}" rel="{$val.img_url}" />
								        <!-- {/foreach} -->
							      	</div>
							    </div>
							    {else}
							    <div id="mainbody">
							    	<img src="{$no_picture}" id="mainphoto" />
							    </div>
							    {/if}
							</div>
						</div>
					</div>
					
					{if $attr_list}
					<div id="attributes">
						<div class="attributes-list" id="J_AttrList">
							<div class="tm-clear tb-hidden tm_brandAttr" id="J_BrandAttr" style="display: block;">
								<p class="attr-list-hd tm-clear"><a class="ui-more-nbg tm-MRswitchAttrs" href="#J_Attrs"><i class="ui-more-nbg-arrow tm-MRswitchAttrs"></i></a><em>产品参数：</em></p>
								<ul id="J_AttrUL">
									<!-- {foreach from=$attr_list item=val} -->
				         			<li>{$val.attr_name}：{$val.attr_value}</li>       			
				         			<!-- {/foreach} -->																			    						    						    							    						    							    							    						    							    						    							    						    							    					    					    																																																																																																																											    								     <li title="&nbsp;32GB&nbsp;128GB&nbsp;256GB">存储容量:&nbsp;32GB&nbsp;128GB&nbsp;256GB</li>
								</ul>
							</div>
						</div>
					</div>
					{/if}
					<div class="t_c clear">{$goods.goods_desc}</div>
				</div>
			</div>
		</div>
	</div>
</div>


<!-- {/block} -->