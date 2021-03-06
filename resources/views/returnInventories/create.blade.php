@extends('layouts.app')

@section('content')
    <div class="col-md-12">
        <h1 class="text-center">Add a Return</h1>
        <hr>
        {!! Form::open(['action' => 'ReturnInventoriesController@store', 'method' => 'POST', 'autocomplete' => 'off']) !!}
        <div class="col-md-12">
            <div id="productInformationContainer" class="container">

                    <h3 class="text-center">Product Information</h3>
                    <div class="col-md-12 row">
                        <div class="form-group col-md-3" id="inventoryIdContainer">
                            {{Form::label('inventoryId', 'Inventory Id')}}
                            {{Form::number('inventoryId', '', ['class' => 'form-control', 'placeholder' => 'Inventory Id', 'readonly', 'id' => 'inventoryId', 'required'])}}
                        </div>
                        <div class="form-group col-md-3" id="productIdContainer">
                            {{Form::label('productId', 'Product Id')}}
                            {{Form::number('productId', '', ['class' => 'form-control', 'placeholder' => 'Product Id', 'readonly', 'id' => 'productId', 'required'])}}
                        </div>
                        <div class="form-group col-md-3" id="saleIdContainer">
                            {{Form::label('saleId', 'Sale #')}}
                            {{Form::number('saleId', '', ['class' => 'form-control', 'placeholder' => 'Sale #','min' => 0,'step'=>1, 'id' => 'saleId', 'required'])}}
                        </div>
                        <div class="form-group col-md-3">
                        {{Form::label('brandName', 'Brand Name')}}
                        {{-- {{Form::select('brandName', [] , null, ['class' => 'form-control', 'placeholder' => 'Pick a product...', 'id' => 'brandName', 'required'])}} --}}
                        <select name="brandName" class="form-control" id="brandName" disabled required><option disabled value="" selected>Choose a product...</option></select>
                        </div>
                        <div class="form-group  col-md-6">
                            {{Form::label('genericName', 'Generic Name')}}
                            {{Form::text('genericName', '', ['class' => 'form-control', 'placeholder' => 'Generic Name', 'readonly', 'id' => 'genericName', 'required'])}}
                        </div>
                    </div>
                    <div class="col-md-12 row">
                        <div class="form-group col-md-3">
                            {{Form::label('quantity', 'Quantity')}}
                            {{Form::number('quantity', '', ['class' => 'form-control', 'min' => 0 ,'placeholder' => 'Quantity', 'id' => 'quantity', 'required', 'disabled'])}}
                        </div>
                        <div class="form-group col-md-3">
                            {{Form::label('expirationDate', 'Expiration Date')}}
                            {{-- <select name="expirationDate" id="expirationDate" required class="form-control"></select> --}}
                            {{Form::text('expirationDate', '', ['class' => 'form-control expirationSelect', 'placeholder' => 'Expiration date', 'id' => 'expirationDate', 'readonly'])}}
                        </div>
                        <div class="form-group  col-md-3">
                            {{Form::label('manufacturer', 'Manufacturer')}}
                            {{Form::text('manufacturer', '', ['class' => 'form-control', 'placeholder' => 'Manufacturer', 'readonly', 'id' => 'manufacturer', 'required'])}}
                        </div>
                        <div class="form-group col-md-3">
                            {{Form::label('drugType', 'Drug Type')}}
                            {{Form::text('drugType', '', ['class' => 'form-control', 'placeholder' => 'Drug Type', 'type' => 'text', 'readonly', 'id' => 'drugType', 'required'])}}
                        </div>
                    </div>
                </div>

            <hr>

            <a class="btn btn-info" href="/returns">Cancel</a>
            {{ Form::submit('Add Return', ['class' => 'btn btn-primary'])}}
        </div>

        {!! Form::close() !!}

    </div>
@endsection


@section('formLogic')
    <script>
        $('document').ready(function(){
            console.log('Page is ready.');

            $('#productIdContainer').hide();
            $('#inventoryIdContainer').hide();

            // Initial Variables;
            var productsToShow;

            // For searching Brand Name
            $('#brandName').change(function(){
                searchProductSaleInfo($(this).val(), $('#saleId').val());
                if($(this).val() != ''){
                    $('#quantity').attr({
                        'disabled': false
                    });
                }else{
                    $('#quantity').attr({
                        'disabled': true
                    });
                }
                // console.log($(this).val());
            });

            $('#saleId').keyup(function (){
                searchBySaleId($(this).val());

                $('#expirationDate').val(''); 
                $('#productId').val('');
                $('#genericName').val('');
                $('#manufacturer').val('');
                $('#drugType').val('');
                $('#quantity').val('');

                    
                $('#quantity').attr({
                    'disabled': true
                });
                
                // if($(this).val() != ''){
                //     $('#brandName').attr({
                //         'disabled': false
                //     });
                // }else{
                //     $('#brandName').attr({
                //         'disabled': true
                //     });
                // }
            })

            $("#quantity").keyup(function(){
                if(parseInt($(this).val()) > parseInt($(this).attr('max'))){
                    $(this).val($(this).attr('max'));
                    return;
                }
            });

            // Function for getting the product information
            function searchProductSaleInfo(productId, saleId){
                $.ajax({
                    url: '/searchProductSaleInfo',
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: productId,
                        saleId: saleId
                    },
                    success: function (msg) {

                        // if the response is not null
                        if(msg['product'] != null){
                            console.log(msg);

                            $('#productId').val(msg['product']['id']);
                            $('#genericName').val(msg['genericNames']['description']);
                            $('#manufacturer').val(msg['manufacturers']['name']);
                            $('#drugType').val(msg['drugTypes']['description']);
                            $('#quantity').attr({
                                'max': msg['productSale']['quantity'],
                                'min': 0
                            });
                            $('#quantity').val(msg['productSale']['quantity']);

                            $('#expirationDate').val(msg['inventory']['expiration_date']);
                            $('#inventoryId').val(msg['inventory']['id']);
                        }else{

                            $('#expirationDate').val(''); 
                            $('#productId').val('');
                            $('#genericName').val('');
                            $('#manufacturer').val('');
                            $('#drugType').val('');
                            $('#quantity').val('');
                            $('#inventoryId').val('');
                        }
                    }
                });
            }

            function searchBySaleId(saleId){
                $.ajax({
                    url: '/searchBySaleId',
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        saleId: saleId
                    },
                    success: function (msg) {

                        $('#brandName').html('<option disabled value="" selected>Choose a product...</option>');

                        if(msg['sales'] != '' && msg['sales'] != null){
                            console.log(msg);
                            $('#brandName').attr({
                                "disabled": false
                            });

                            $('#brandName').html('');
                            
                            var optionOutput = '<option disabled value="" selected>Choose a product...</option>';

                            for (var i = 0; i < msg['sales'].length; i++) {
                                optionOutput += '<option value="' + msg['sales'][i]['product']['id'] + '">' + msg['sales'][i]['product']['brand_name'] + '</option>';
                            }

                            $('#brandName').append(optionOutput);
                        }else{
                            $('#brandName').attr({
                                'disabled': true
                            });
                        }
                    }
                });
            }

            function autocomplete(inp, arr) {
                /*the autocomplete function takes two arguments,
                the text field element and an array of possible autocompleted values:*/
                var currentFocus;
                /*execute a function when someone writes in the text field:*/
                inp.addEventListener("input", function (e) {
                    var a, b, i, val = this.value;
                    /*close any already open lists of autocompleted values*/
                    closeAllLists();
                    if (!val) {
                        return false;
                    }
                    currentFocus = -1;
                    /*create a DIV element that will contain the items (values):*/
                    a = document.createElement("DIV");
                    a.setAttribute("id", this.id + "autocomplete-list");
                    a.setAttribute("class", "autocomplete-items");
                    /*append the DIV element as a child of the autocomplete container:*/
                    this.parentNode.appendChild(a);
                    /*for each item in the array...*/
                    for (i = 0; i < arr.length; i++) {
                        /*check if the item starts with the same letters as the text field value:*/
                        if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                            /*create a DIV element for each matching element:*/
                            b = document.createElement("DIV");
                            /*make the matching letters bold:*/
                            b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                            b.innerHTML += arr[i].substr(val.length);
                            /*insert a input field that will hold the current array item's value:*/
                            b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
                            /*execute a function when someone clicks on the item value (DIV element):*/
                            b.addEventListener("click", function (e) {
                                /*insert the value for the autocomplete text field:*/
                                inp.value = this.getElementsByTagName("input")[0].value;
                                /*close the list of autocompleted values,
                                (or any other open lists of autocompleted values:*/
                                closeAllLists();
                            });
                            a.appendChild(b);
                        }
                    }
                });
                /*execute a function presses a key on the keyboard:*/
                inp.addEventListener("keydown", function (e) {
                    var x = document.getElementById(this.id + "autocomplete-list");
                    if (x) x = x.getElementsByTagName("div");
                    if (e.keyCode == 40) {
                        /*If the arrow DOWN key is pressed,
                        increase the currentFocus variable:*/
                        currentFocus++;
                        /*and and make the current item more visible:*/
                        addActive(x);
                    } else if (e.keyCode == 38) { //up
                        /*If the arrow UP key is pressed,
                        decrease the currentFocus variable:*/
                        currentFocus--;
                        /*and and make the current item more visible:*/
                        addActive(x);
                    } else if (e.keyCode == 13) {
                        /*If the ENTER key is pressed, prevent the form from being submitted,*/
                        e.preventDefault();
                        if (currentFocus > -1) {
                            /*and simulate a click on the "active" item:*/
                            if (x) x[currentFocus].click();
                        }
                    }
                });

                function addActive(x) {
                    /*a function to classify an item as "active":*/
                    if (!x) return false;
                    /*start by removing the "active" class on all items:*/
                    removeActive(x);
                    if (currentFocus >= x.length) currentFocus = 0;
                    if (currentFocus < 0) currentFocus = (x.length - 1);
                    /*add class "autocomplete-active":*/
                    x[currentFocus].classList.add("autocomplete-active");
                }

                function removeActive(x) {
                    /*a function to remove the "active" class from all autocomplete items:*/
                    for (var i = 0; i < x.length; i++) {
                        x[i].classList.remove("autocomplete-active");
                    }
                }

                function closeAllLists(elmnt) {
                    /*close all autocomplete lists in the document,
                    except the one passed as an argument:*/
                    var x = document.getElementsByClassName("autocomplete-items");
                    for (var i = 0; i < x.length; i++) {
                        if (elmnt != x[i] && elmnt != inp) {
                            x[i].parentNode.removeChild(x[i]);
                        }
                    }
                }
                /*execute a function when someone clicks in the document:*/
                document.addEventListener("click", function (e) {
                    closeAllLists(e.target);
                });
            }
        });
    </script>
@endsection