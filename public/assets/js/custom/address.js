$(document).ready((function(){function o(){var o=parseFloat($("#cost").val()),a=$("#markup_type").val(),e=parseFloat($("#markup").val());if(isNaN(o)||isNaN(e))return $("#srp").val(0),!1;var t=o+e;"percentage"==a&&(t=o+o*(e/100)),$("#srp").val(t)}function a(o){var a='<option value="">Select a '+o+"</option>";$("#"+o).empty(),$("#"+o).html(a),$("#"+o).select2()}function e(o,a){$("#"+o).empty(),$("#"+o).html(a),$("#"+o).select2()}$("#markup_type").on("change",(function(){o()})),$(".compute-srp").on("keyup",(function(){o()})),$(".department-category-selector").on("change",(function(){var o=$(this).val(),t="";a("category_id"),$(".category-subcategory-selector").length&&a("subcategory_id"),$.ajax({url:"/ajax/get-department-categories",method:"GET",data:{department_id:o},success:function(o){t+='<option value="">Select a category</option>',o.forEach((function(o){t+='<option value="'+o.id+'">'+o.name+"</option>"})),e("category_id",t)},error:function(o,a,e){console.error("Error:",e)}})})),$(".category-subcategory-selector").on("change",(function(){var o=$(this).val(),t="";a("subcategory_id"),$.ajax({url:"/ajax/get-category-subcategories",method:"GET",data:{category_id:o},success:function(o){t+='<option value="">Select a subcategory</option>',o.forEach((function(o){t+='<option value="'+o.id+'">'+o.name+"</option>"})),e("subcategory_id",t)},error:function(o,a,e){console.error("Error:",e)}})})),$(".company-cluster-selector").on("change",(function(){var o=$(this).val(),t="";a("cluster_id"),$.ajax({url:"/ajax/get-clusters",method:"GET",data:{company_id:o},success:function(o){t+='<option value="">Select a cluster</option>',o.forEach((function(o){t+='<option value="'+o.id+'">'+o.name+"</option>"})),e("cluster_id",t)},error:function(o,a,e){console.error("Error:",e)}})})),$("#region_id").on("change",(function(){var o=$(this).val(),t="";a("province_id"),a("city_id"),a("barangay_id"),$.ajax({url:"/ajax/get-provinces",method:"GET",data:{region_id:o},success:function(o){t+='<option value="">Select a province</option>',o.forEach((function(o){t+='<option value="'+o.id+'">'+o.name+"</option>"})),e("province_id",t)},error:function(o,a,e){console.error("Error:",e)}})})),$("#province_id").on("change",(function(){var o=$(this).val(),t="";a("city_id"),a("barangay_id"),$.ajax({url:"/ajax/get-cities",method:"GET",data:{province_id:o},success:function(o){t+='<option value="">Select a city</option>',o.forEach((function(o){t+='<option value="'+o.id+'">'+o.name+"</option>"})),e("city_id",t)},error:function(o,a,e){console.error("Error:",e)}})})),$("#city_id").on("change",(function(){var o=$(this).val(),t="";a("barangay_id"),$.ajax({url:"/ajax/get-barangays",method:"GET",data:{city_id:o},success:function(o){t+='<option value="">Select a barangay</option>',o.forEach((function(o){t+='<option value="'+o.id+'">'+o.name+"</option>"})),e("barangay_id",t)},error:function(o,a,e){console.error("Error:",e)}})}))}));
