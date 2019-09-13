{*
* @author Victor Santillan <santillan-15@live.com>
* @copyright 2019-2025 Victor Santillan
* @license Property Victor Santillan
*}
<section>
  <h1>{l s='Our Products' d='Modules.CarouselProducts.Shop'}</h1>
  <div class="products" id="carouselproducts">
    {foreach from=$products item="product"}
    <div class="{$classes}">
      <center>
      {include file="catalog/_partials/miniatures/product.tpl" product=$product}
      </center>
    </div>
    {/foreach}

  </div>
  <div class="col-xs-12">
    <a class="all-product-link float-xs-left float-md-right h4" href="{$allProductsLink}">
      {l s='All products' d='Modules.CarouselProducts.Shop'}
    </a>
  </div>
</section>
<script>
	var numberProducts = {$number_products_row},
      isCarousel = ("{$carousel}" != "") ? true : false,
      speed = {$speed},
      autoplay = ("{$autoplay}") != "" ? true : false,
      centermode = ("{$centermode}" != "") ? true : false,
      toscroll = {$toscroll},
      infinite = ("{$infinite}" != "") ? true : false,
      dots = ("{$dots}" != "") ? true :  false,
      xsss = {$xsss},
      xssc = {$xssc},
      smss = {$smss},
      smsc = {$smsc},
      mdss = {$mdss},
      mdsc = {$mdsc};
</script>