@extends('layouts.app')

@section('content')
    <div class="col-md-12 responsive">

        <div class="form-group">
        <h1 class="text-center"><span class="fa fa-list-alt">&nbsp;</span>Inventories</h1>
        @if (Auth::user()->role == 'Administrator')
            <a class="btn btn-primary" href="/inventories/create"><span class="fa fa-plus"></span>&nbsp;Inventory</a>
        @endif
        <div class="input-group my-3">
            <input type="text" class="form-control" placeholder="Search" id="searchBatchNumber">
            <div class="input-group-append">
                <button class="btn btn-primary" id="btnSearchBatch" type="">Go</button> 
            </div>
        </div>

        @foreach ($batches as $batch)
            @if ($batch->inventories->count() > 0)
                <div class="table-responsive rounded my-2" id="tableContainer">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="thead-dark table-sm">
                            <tr class="">
                                <th colspan="8" class="align-middle"><small>Batch Number: </small>{{ $batch->id }}</th>
                                </tr>
                        </thead>
                        <thead class="thead-dark table-sm">
                            <tr class="text-center small">
        
                                <th>Generic Name</th>
                                <th>Brand Name</th>
                                <th>Supplier</th>
                                <th>Quantity</th>
                                <th>Sold</th>
                                <th>Remaining Stocks</th>
                                <th>Delivery Date</th>
                                <th>Expiration Date</th>
                            </tr>
                        </thead>
                        <tbody class="table-sm">
                            @foreach ($batch->inventories->sortBy('product.genericNames.description') as $inventory)
                                <tr class="align-middle text-center small">
                                    <td class="align-middle">{{ $inventory->product->genericNames->description }}</td>
                                    <td class="align-middle">{{ $inventory->product->brand_name }}</td>
                                    <td class="align-middle"><a class="modalSupplierClass" href="#" role="button" data-toggle="modal" data-target="#modalSupplier" data-supplier-id="{{ $inventory->supplier->id}}">{{ $inventory->supplier->name }}</a></td>
                                    <td class="align-middle">{{ $inventory->quantity }}</td>
                                    <td class="align-middle">{{ $inventory->sold }}</td>
                                    <td class="align-middle">{{ $inventory->quantity - $inventory->sold }}</td>
                                    <td class="align-middle">{{ Carbon\Carbon::parse($inventory->delivery_date)->toFormattedDateString() }}</td>
                                    <td class="align-middle">{{ Carbon\Carbon::parse($inventory->expiration_date)->toFormattedDateString() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endforeach
        </div>
        {{ $batches->links()}}
    </div>


    <div id="modalSupplier" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        {{Form::label('supplierName', 'Supplier Name', ['id' => 'supplierName'])}}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <div class="form-group">
                            <small>{{Form::label('address', 'Address')}}</small>
                            {{Form::text('address', '', ['class' => 'form-control', 'placeholder' => 'Address', 'disabled' => true, 'id' => 'address'])}}
                        </div>
                        <div class="form-group">
                            <small>{{Form::label('ltoNumber', 'LTO Number')}}</small>
                            {{Form::number('ltoNumber', '' , ['class' => 'form-control', 'placeholder' => 'LTO Number', 'disabled' => true, 'id' => 'ltoNumber'])}}
                        </div>
                        <div class="form-group">
                            <small>{{Form::label('expirationDate', 'Expiration Date')}}</small>
                            {{Form::text('expirationDate', '' , ['class' => 'form-control', 'placeholder' => 'Expiration Date', 'disabled' => true, 'id' => 'expirationDate'])}}
                        </div>
                        <div class="form-group">
                            <small>{{Form::label('contactPerson', 'Contact Person')}}</small>
                            {{Form::text('contactPerson', '', ['class' => 'form-control', 'placeholder' => 'Contact Person', 'disabled' => true, 'id' => 'contactPerson'])}}
                        </div>
                        <div class="form-group">
                            <small>{{Form::label('contactNumber', 'Contact Number')}}</small>
                            {{Form::text('contactNumber', '' , ['class' => 'form-control', 'placeholder' => 'Contact Number', 'disabled' => true, 'id' => 'contactNumber'])}}
                        </div>
                        <div class="form-group">
                            <small>{{Form::label('emailAddress', 'Email Address')}}</small>
                            {{Form::text('emailAddress', '' , ['class' => 'form-control', 'placeholder' => 'Email Address', 'disabled' => true, 'id' => 'emailAddress'])}}
                        </div>
                    </div>
                </div>
                <div class="modal-footer" id="modalSupplierFooter">
                    
                </div>
            </div>
        </div>
    </div>
    
@endsection

@section('formLogic')
    <script>
        $('document').ready(function(){
            console.log('Page is ready');

            $(".modalSupplierClass").click(function () {
                var supplierId = $(this).data('supplier-id');
                searchSupplierInfoById(supplierId);
            });
            
            $('#btnSearchBatch').click(function(){
                console.log('imclick.');
                window.location.href = '/inventories/' + $('#searchBatchNumber').val();
            });

        });

        function searchSupplierInfoById(supplierId) {
            $.ajax({
                url: '/searchSupplierInfoById',
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: supplierId
                },
                success: function (msg) {
                    console.log(msg);

                    // if the response is not null

                    if (msg['supplier'] != null) {

                        // modalSupplier
                        $('#supplierName').html(msg['supplier']['name']);
                        $('#address').val(msg['supplier']['address']);
                        $('#emailAddress').val(msg['supplier']['email_address']);
                        $('#ltoNumber').val(msg['supplier']['lto_number']);
                        $('#expirationDate').val(msg['supplier']['expiration_date']);
                        $('#contactPerson').val(msg['supplier']['contact_person']);
                        $('#contactNumber').val(msg['supplier']['contact_number']);
                        $('#modalSupplierFooter').html('');
                        $('#modalSupplierFooter').append('<a class="btn btn-info col-md-12" href=/suppliers/' + msg['supplier']['id'] + '><span class="fa fa-info-circle">&nbsp;</span>View Supplier Information</a>');
                    }
                }
            });
        }
    </script>
@endsection