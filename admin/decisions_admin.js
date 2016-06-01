 const MULTI = 1;

 var resources;

 var apiUrl = "../api";

 var resourceObjects = [];



 $(document).ready(function() {



     $.ajax({

         url: apiUrl + '/admin/resources',

         method: "get",


         headers: {
            "Authorization": "Basic " + btoa("t00r" + ":" + "passw0rd")
          },
         success: function(responce) {



             resources = responce;



             $.each(resources, function(i, resource) {



                 if(resource.dependency == false){



                        var button = $("<button>" + resource.title + "</button>");



                     button.click(function() {



                         $('#main').empty();

                         new Resource($('#main'), resource);

                     });



                     $('#menu').append(button);

                }

             });



         }



     });



 });







 function zeroPadded(val) {

    

     if (val >= 10) return val;



     else {

         return "0" + val;

     }



 }





 var Resource = function(place, resource) {

     this.place = place;



     this.createDetailsTable = function(resource) {

         var that = this;



         $.each(resource.insert.columns, function(i, column) {

             var el = $("<div></div>");

             var element;

             var label = "<label for=\"" + column.column + "\">" + column.title + "</label>";



             if (column.type == "datetime") {

                 element = $("<input name=\"" + column.column + "\" type=\"datetime-local\">");



             } else if (column.type == "bool") {

                 element = $("<input name=\"" + column.column + "\" type=\"checkbox\">");



             } else if (column.type == "select") {

                 element = $("<select name=\"" + column.column + "\"></select>");



                 if (column.resource != null) {



                     $.ajax({

                         type: "GET",

                         url: apiUrl + column.resource,



                         success: function(data) {



                             $.each(data, function(i, d) {

                                 element.append('<option value="' + d.id + '">' + d.title + '</option>');

                             });



                         }



                     });



                 }





             } else {



                 element = $("<input name=\"" + column.column + "\" type=\"text\">");

             

             }



             element.attr('id', field_id(column.column));

             that.field_tags[column.column] = element;

             that.form.append(label);

             that.form.append(element);

             //alert(JSON.stringify(element));

             //form.append(el);                             

         });



         that.submit = $("<button class=\"submit\"> Add </submit>");

         that.cancel = $("<button > Cancel </submit>");



         that.submit.click(function() {

             var data = that.form.serialize();



             $.ajax({

                 url: apiUrl + resource.url + (that.mode == "add" ? "" : "/" + that.edit_id),

                 type: that.mode == "add" ? "post" : "put",

                 data: data,



                 success: function(responce) {

                     that.mode = "add";

                     that.submit.html("Add");

                     that.form.trigger("reset");

                     that.table.ajax.reload();

                 }



             });



             return false;

         });



         that.form.append(that.submit);



         that.cancel.click(function() {

             form.empty();



             if (that.mode == "edit") {

                 that.mode = "add";

                 that.submit.html("Add");

                 that.hidedependencies();

             }



             return false;

         });



         that.form.append(that.cancel);

         that.details_form.append(that.form);

     }





     this.hidedependencies = function() {



         if (this.dependencies_tag != null) {

             this.dependencies_tag.empty();

         }



     }





     this.showdependencies = function() {

         var that = this;



         if (this.dependencies_tag == null) {

             this.dependencies_tag = $("<div class=\"dependencies\"></div");

             this.place.append(this.dependencies_tag);

         }



         this.dependencies_tag.empty();



         $.each(this.resource.dependencies, function(i, initial_dependence) {



             var dependence = jQuery.extend(true, {}, initial_dependence);

             var dependence_tag = $("<div></div>");

             dependence_tag.append("<h3>" + (dependence.title) + "</h3>");

             var dependence_place = $("<div></div>");

             that.dependencies_tag.append(dependence_tag);

             that.dependencies_tag.append(dependence_place);

             dependence.url = dependence.url.replace(":parent", that.edit_id);

             new Resource(dependence_place, dependence);



         });



     }





     this.field_tags = {};

     //this.table = null;

     this.mode = "add";

     var that = this;

     //var submit, cancel, edit_id;

     this.form = $("<form></form>");

     this.details_form = $("<div id=\"details_form\"></div>");

     this.details_form.append(this.form);

     this.list = $("<div id=\"list\"></div>");

     this.table_tag = $("<table id=\"list_table\" class=\"display\" cellspacing=\"0\"><thead></thead></table>");

     this.list.append(this.table_tag);

     place.append(this.details_form);

     place.append(this.list);

     this.resource = resource;

     this.columns = resource.columns;



     this.columns.push({

         "className": 'delete',

         "orderable": false,

         "data": null,

         "defaultContent": '<button  class="edit">edit</button>'

     });





     this.columns.push({

         "className": 'delete',

         "orderable": false,

         "data": null,

         "defaultContent": '<button class="delete">delete</button>'

     });





     this.table = this.table_tag.DataTable({

         "ajax": apiUrl + this.resource.url,

         "columns": this.columns,



         "fnRowCallback": function(nRow, aData, iDisplayIndex) {



             /*if (that.resource.row_actions != null) {









                 $.each(that.resource.row_actions, function(i, action) {







                     var button = $('<button></button>');





                     button.click(function() {







                         eval(action.onClick);







                     })







                     button.html(action.title);



                     $('td:eq(' + (that.columns.length - 1) + ')', nRow).append(button);







                 });









             }*/

             return nRow;

         },



     });





     $(that.table_tag).on('click', 'button.delete', function() {



         if (confirm("Do you want to delete")) {

             var aData = that.table.row($(this).parents('tr')).data();

             alert(JSON.stringify(aData));



             $.ajax({

                 url: apiUrl + resource.url + "/" + aData[that.resource.key],

                 method: "delete",



                 success: function(responce) {

                     that.table.ajax.reload();

                 }



             });



         }



     });





     $(that.table_tag).on('click', 'button.edit', function() {

         var aData = that.table.row($(this).parents('tr')).data();



         $.each(that.resource.insert.columns, function(i, column) {

             var value = "";

             var id = field_id(column.column);

             value = aData[column.column];



             if (column.type == "datetime" && value != null) {

                 var t = value.split(/[- :]/);

                 var d = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);

                 var df = "" + d.getFullYear() + "-" + zeroPadded(d.getMonth() + 1) + "-" + zeroPadded(d.getDate()) + "T" + zeroPadded(d.getHours()) + ":" + zeroPadded(d.getMinutes()) + ":" + zeroPadded(d.getSeconds());

                 that.field_tags[column.column].val(df);



             } else if (column.type == "bool") {

                 that.field_tags[column.column].prop("checked", value == '1' ? true : false);



             } else {

                 that.field_tags[column.column].val(value);

             }



         });



         that.mode = "edit";

         that.edit_id = aData[resource.key];

         that.submit.html("Edit");

         that.showdependencies();

     });



     this.form.empty();

     this.createDetailsTable(this.resource);

 }









 function field_id(name) {



     return 'field_' + name;



 }