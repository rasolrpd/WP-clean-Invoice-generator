<?php
/*
Plugin Name: Woo Invoice Shortcode
Description: نمایش فاکتور سفارش ووکامرس با قابلیت چاپ و دانلود
Version: 2.5
*/

if (!defined('ABSPATH')) exit;

// اضافه کردن دکمه "نمایش فاکتور" در جدول سفارش‌های حساب کاربری
add_filter('woocommerce_my_account_my_orders_actions', 'add_invoice_button_to_my_orders', 10, 2);

function add_invoice_button_to_my_orders($actions, $order) {

    // اگر سفارش معتبر نبود کاری نکنیم
    if (!$order) return $actions;

    // آدرس صفحه نمایش فاکتور → این را با صفحه خودت جایگزین کن
    $invoice_url = site_url('/order-summary/?order_id=' . $order->get_id());

    // دکمه جدید را اضافه می‌کنیم
    $actions['view-invoice'] = array(
        'url'  => $invoice_url,
        'name' => 'نمایش فاکتور',
        'type' => 'view-invoice',
    );

    return $actions;
}

/* ===============================
لود اسکریپت html2canvas
=============================== */

add_action('wp_enqueue_scripts', function () {

    if(isset($_GET['order_id']) || is_order_received_page()){

        wp_enqueue_script(
            'html2canvas',
            plugins_url('assets/js/html2canvas.min.js', __FILE__),
            [],
            '1.4.1',
            true
        );

    }

});


/* ===============================
دکمه مشاهده فاکتور در ادمین
=============================== */

add_action('woocommerce_admin_order_data_after_order_details','woo_invoice_admin_button');

function woo_invoice_admin_button($order){

$order_id = $order->get_id();

$url = site_url('/order-summary/?order_id='.$order_id);

echo '<p style="margin-top:15px">
<a href="'.$url.'" target="_blank" class="button button-primary">
مشاهده خلاصه سفارش
</a>
</p>';

}


/* ===============================
شورتکد فاکتور
=============================== */

add_shortcode('woo_invoice_table','woo_invoice_table_shortcode');

function woo_invoice_table_shortcode(){

$order=false;


/* حالت ادمین */

if(isset($_GET['order_id'])){

$order_id=intval($_GET['order_id']);
$order=wc_get_order($order_id);

}


/* صفحه تشکر */

elseif(is_order_received_page() && !empty($_GET['key'])){

$order_id=wc_get_order_id_by_order_key(
sanitize_text_field($_GET['key'])
);

$order=wc_get_order($order_id);

}

if(!$order){
return '';
}


$site_name=get_bloginfo('name');

$logo_url='https://iripaksam.ir/wp-content/uploads/2026/05/photo26742879376.png';

$year=function_exists('jdate') ? jdate('Y') : date_i18n('Y');

$invoice_number=$order->get_id().'-'.$year;

$buyer_name=$order->get_billing_first_name().' '.$order->get_billing_last_name();

$buyer_phone=$order->get_billing_phone();

ob_start();
?>


<style>

#print-area{
max-width:900px;
margin:20px auto;
background:#fff;
padding:20px;
border:1px solid #ddd;
direction:rtl;
font-family:tahoma;
}

.invoice-table-wrap{
width:100%;
overflow-x:auto;
}

#print-area table{
width:100%;
border-collapse:collapse;
margin-bottom:15px;
min-width:600px;
}

#print-area th,
#print-area td{
border:1px solid #ccc;
padding:8px;
text-align:center;
font-size:14px;
}

#print-area thead{
background:#bdbdbd;
color:#fff;
}

.print-btn{
margin-top:20px;
padding:12px 25px;
background:#222;
color:#fff;
border:none;
cursor:pointer;
font-size:16px;
border-radius:4px;
}

.print-btn:hover{
background:#000;
}

@media (max-width:768px){

#print-area{
padding:12px;
margin:10px;
}

#print-area th,
#print-area td{
font-size:12px;
padding:6px;
}

.print-btn{
width:100%;
}

}
.invoice-buttons{
    display:flex;
    justify-content:center;
    gap:10px;          /* فاصله دقیق بین دکمه‌ها */
    margin-top:20px;
}

.invoice-buttons .print-btn{
    margin:0 !important;   /* حذف فاصله ناخواسته */
}

.invoice-buttons .dark{
    background:#444;
}

@media (max-width:768px){
    .invoice-buttons{
        flex-direction:column;
    }
}

</style>



<div id="print-area">


<div style="text-align:center;margin-bottom:25px">

<h2><?php echo esc_html($site_name); ?></h2>

<?php if($logo_url){ ?>
<img src="<?php echo esc_url($logo_url); ?>" style="width:70px;height:70px;margin-top:10px;">
<?php } ?>

</div>



<div class="invoice-table-wrap">

<table>

<tr>

<td><strong>شماره فاکتور</strong></td>
<td><?php echo $invoice_number; ?></td>

<td><strong>تاریخ سفارش</strong></td>
<td><?php echo wc_format_datetime($order->get_date_created()); ?></td>

</tr>

</table>

</div>



<div class="invoice-table-wrap">

<table>

<tr>
<th colspan="2">مشخصات فروشنده</th>
</tr>

<tr>
<td>نام فروشنده</td>
<td>شهرام عباس زاده</td>
</tr>

<tr>
<td>شماره ملی</td>
<td>4679004975</td>
</tr>

<tr>
<td>شماره اقتصادی</td>
<td>46790049750001</td>
</tr>

<tr>
<td>شماره تماس</td>
<td>09139036077</td>
</tr>

</table>

</div>



<div class="invoice-table-wrap">

<table>

<tr>
<th colspan="2">مشخصات خریدار</th>
</tr>

<tr>
<td>نام خریدار</td>
<td><?php echo esc_html($buyer_name); ?></td>
</tr>

<tr>
<td>شماره تماس</td>
<td><?php echo esc_html($buyer_phone); ?></td>
</tr>

</table>

</div>



<h4 style="margin-top:25px">
شرح اقلام خریداری شده
</h4>



<div class="invoice-table-wrap">

<table>

<thead>

<tr>
<th>شرح کالا</th>
<th>مقدار</th>
<th>قیمت واحد</th>
<th>قیمت کل</th>
</tr>

</thead>

<tbody>

<?php foreach ($order->get_items() as $item):

$name=$item->get_name();
$qty=$item->get_quantity();
$total=$item->get_total();
$unit=($qty>0)?$total/$qty:0;

?>

<tr>

<td><?php echo esc_html($name); ?></td>
<td><?php echo $qty; ?></td>
<td><?php echo wc_price($unit); ?></td>
<td><?php echo wc_price($total); ?></td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>



<div style="text-align:left;font-size:18px;font-weight:bold">

مبلغ کل :
<?php echo $order->get_formatted_order_total(); ?>

</div>


</div>



<!-- دکمه‌ها خارج از اسکرین -->

<div style="text-align:center">
 
<div class="invoice-buttons">
    <button class="print-btn" onclick="printInvoiceSnapshot()">چاپ فاکتور</button>
    <button class="print-btn dark" onclick="downloadInvoiceImage()">دانلود فاکتور</button>
</div>


</div>



<script>

document.addEventListener("DOMContentLoaded",function(){


window.captureInvoice=function(callback){

const container=document.getElementById('print-area');

const originalWidth=container.style.width;
const originalTransform=container.style.transform;


/* حالت دسکتاپ اجباری */

container.style.width='900px';
container.style.transform='scale(1)';
container.style.transformOrigin='top right';


html2canvas(container,{
scale:2,
useCORS:true,
backgroundColor:"#ffffff",
windowWidth:1200
}).then(function(canvas){

const img=canvas.toDataURL("image/png");


/* بازگردانی استایل */

container.style.width=originalWidth;
container.style.transform=originalTransform;

if(callback){
callback(img);
}

});

}



window.printInvoiceSnapshot=function(){

captureInvoice(function(img){

let win=window.open("");

win.document.write(`
<html>
<head>
<title>Invoice</title>
<style>
body{margin:0;text-align:center}
img{width:100%;height:auto}
</style>
</head>
<body onload="window.print();window.close();">
<img src="${img}">
</body>
</html>
`);

win.document.close();

});

}



window.downloadInvoiceImage=function(){

captureInvoice(function(img){

const link=document.createElement('a');

link.href=img;

link.download='invoice-<?php echo $order->get_id();?>-<?php echo $year;?>.png';

document.body.appendChild(link);

link.click();

document.body.removeChild(link);

});

}


});

</script>


<?php
return ob_get_clean();

}
