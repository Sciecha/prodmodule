{if isset($products) && $products}
    <section class="featured-products">
        <h2>{$category} {l s='Product List'}</h2>
        <div class="products">
                {include file="$tpl_dir./product-list.tpl" product=$product}
        </div>
    </section>
{/if}
