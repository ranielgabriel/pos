@extends('layouts.app')

@section('content')
    <div class="col-md-12 responsive">
        <div class="form-group">
            <h1 class="text-center"><span class="fa fa-exchange">&nbsp;</span>Returns</h1>
            @guest

            @else
                @if($user->role == 'Administrator')
                    <a class="btn btn-primary" href="/returns/create"><span class="fa fa-plus"></span>&nbsp;Return</a>
                @endif
            @endguest

            <div class="input-group mb-3 my-2">
                <div class="input-group-prepend">
                    <span class="input-group-text"><span class="fa fa-search"></span></span>
                </div>
                {{Form::text('search', '', ['id' => 'search', 'class' => 'form-control', 'placeholder' => 'Search... (Generic Name / Brand Name / Drug Type / Status)'])}}
            </div>

            <div class="table-responsive rounded" id="tableContainer">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="thead-dark table-sm">
                        <tr class="text-center small">
                            <th class="align-middle">Generic Name</th>
                            <th class="align-middle">Brand Name</th>
                            <th class="align-middle">Drug Type</th>
                            <th class="align-middle">Manufacturer</th>
                            <th class="align-middle">Expiration Date</th>
                            <th class="align-middle">Remaining Stocks</th>
                            <th class="align-middle">Sold</th>
                            <th class="align-middle">Status</th>
                        </tr>
                    </thead>
                    <tbody id="tableReturns" class="table-sm">
                        @foreach ($returnInventories->sortBy('product.brand_name')->sortBy('product.genericNames.description') as $item)
                            <tr class="text-center">
                                <td class="align-middle">{{ $item->product->genericNames->description }}</td>
                                <td class="align-middle">{{ $item->product->brand_name }}</td>
                                <td class="align-middle">{{ $item->product->drugTypes->description }}</td>
                                <td class="align-middle">{{ $item->product->manufacturers->name }}</td>
                                <td class="align-middle">{{ Carbon\Carbon::parse($item->expiration_date)->toFormattedDateString() }}</td>
                                <td class="align-middle">{{ $item->quantity }}</td>
                                <td class="align-middle">{{ $item->sold }}</td>
                                @if($item->quantity == $item->sold)
                                    <td class="align-middle"><center><span class="badge badge-success"> Sold </span></center></td>
                                @else
                                    <td class="align-middle"><center><span class="badge badge-warning"> Selling </span></center></td>
                                @endif
                            </tr>    
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection