@extends('layouts.master')

@section('title')
Sales Transactions
@endsection

@push('css')
<style>
    .display-pay {
        font-size: 5em;
        text-align: center;
        height: 100px;
    }

    .display-spelledout {
        padding: 10px;
        background: #f0f0f0;
    }

    .table-sales tbody tr:last-child {
        display: none;
    }

    @media(max-width: 768px) {
        .display-pay {
            font-size: 3em;
            height: 70px;
            padding-top: 5px;
        }
    }
</style>
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Sales Transactions</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-body">
                    
                <form class="form-product">
                    @csrf
                    <div class="form-group row">
                        <label for="product_code" class="col-lg-2">Product Code</label>
                        <div class="col-lg-5">
                            <div class="input-group">
                                <input type="hidden" name="sales_id" id="sales_id" value="{{ $sales_id }}">
                                <input type="hidden" name="product_id" id="product_id">
                                <input type="text" class="form-control" name="product_code" id="product_code">
                                <span class="input-group-btn">
                                    <button onclick="displayProduct()" class="btn btn-success btn-flat" type="button"><i class="fa fa-search-plus"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="table table-stiped table-bordered table-sales">
                    <thead>
                        <th width="5%">#</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Selling Price</th>
                        <th width="15%">Quantity</th>
                        <th>Discount</th>
                        <th>Subtotal</th>
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                </table>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="display-pay bg-primary"></div>
                        <div class="display-spelledout"></div>
                    </div>
                    <div class="col-lg-4">
                        <form action="{{ route('transaction.save') }}" class="form-sales" method="post">
                            @csrf
                            <input type="hidden" name="sales_id" value="{{ $sales_id }}">
                            <input type="hidden" name="total" id="total">
                            <input type="hidden" name="total_item" id="total_item">
                            <input type="hidden" name="pay" id="pay">
                            <input type="hidden" name="member_id" id="member_id" value="{{ $memberSelected->member_id }}">

                            <div class="form-group row">
                                <label for="totalrp" class="col-lg-2 control-label">Total</label>
                                <div class="col-lg-8">
                                    <input type="text" id="totalrp" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="member_code" class="col-lg-2 control-label">Member</label>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="member_code" value="{{ $memberSelected->member_code }}">
                                        <span class="input-group-btn">
                                            <button onclick="appearMember()" class="btn btn-success btn-flat" type="button"><i class="fa fa-search-plus"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="discount" class="col-lg-2 control-label">Discount</label>
                                <div class="col-lg-8">
                                    <input type="number" name="discount" id="discount" class="form-control" 
                                        value="{{ ! empty($memberSelected->member_id) ? $discount : 0 }}" 
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="pay" class="col-lg-2 control-label">Pay</label>
                                <div class="col-lg-8">
                                    <input type="text" id="payrp" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="accepted" class="col-lg-2 control-label">Received</label>
                                <div class="col-lg-8">
                                    <input type="number" id="accepted" class="form-control" name="accepted" value="{{ $sales->accepted ?? 0 }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="return" class="col-lg-2 control-label">Return</label>
                                <div class="col-lg-8">
                                    <input type="text" id="return" name="return" class="form-control" value="0" readonly>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="box-footer">
                <button type="submit" class="btn btn-success btn-sm btn-flat pull-right btn-simpan"><i class="fa fa-floppy-o"></i> Save Transaction</button>
            </div>
        </div>
    </div>
</div>

@includeIf('sales_detail.product')
@includeIf('sales_detail.member')
@endsection

@push('scripts')
<script>
    let table, table2;

    $(function () {
        $('body').addClass('sidebar-collapse');

        table = $('.table-sales').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('transaction.data', $sales_id) }}',
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'product_code'},
                {data: 'product_name'},
                {data: 'selling_price'},
                {data: 'amount'},
                {data: 'discount'},
                {data: 'subtotal'},
                {data: 'action', searchable: false, sortable: false},
            ],
            dom: 'Brt',
            bSort: false,
            paginate: false
        })
        .on('draw.dt', function () {
            loadForm($('#discount').val());
            setTimeout(() => {
                $('#accepted').trigger('input');
            }, 300);
        });
        table2 = $('.table-product').DataTable();

        $(document).on('input', '.quantity', function () {
            let id = $(this).data('id');
            let amount = parseInt($(this).val());

            if (amount < 1) {
                $(this).val(1);
                alert('The number cannot be less than 1');
                return;
            }
            if (amount > 10000) {
                $(this).val(10000);
                alert('The number cannot exceed 10000');
                return;
            }

            $.post(`{{ url('/transaction') }}/${id}`, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'put',
                    'amount': amount
                })
                .done(response => {
                    $(this).on('mouseout', function () {
                        table.ajax.reload(() => loadForm($('#discount').val()));
                    });
                })
                .fail(errors => {
                    alert('Unable to save data');
                    return;
                });
        });

        $(document).on('input', '#discount', function () {
            if ($(this).val() == "") {
                $(this).val(0).select();
            }

            loadForm($(this).val());
        });

        $('#accepted').on('input', function () {
            if ($(this).val() == "") {
                $(this).val(0).select();
            }

            loadForm($('#discount').val(), $(this).val());
        }).focus(function () {
            $(this).select();
        });

        $('.btn-simpan').on('click', function () {
            $('.form-sales').submit();
        });
    });

    function displayProduct() {
        $('#modal-product').modal('show');
    }

    function hideProduct() {
        $('#modal-product').modal('hide');
    }

    function selectProduct(id, code) {
        $('#product_id').val(id);
        $('#product_code').val(code);
        hideProduct();
        addProduct();
    }

    function addProduct() {
        $.post('{{ route('transaction.store') }}', $('.form-product').serialize())
            .done(response => {
                $('#product_code').focus();
                table.ajax.reload(() => loadForm($('#discount').val()));
            })
            .fail(errors => {
                alert('Unable to save data');
                return;
            });
    }

    function appearMember() {
        $('#modal-member').modal('show');
    }

    function selectMember(id, code) {
        $('#member_id').val(id);
        $('#member_code').val(code);
        $('#discount').val('{{ $discount }}');
        loadForm($('#discount').val());
        $('#accepted').val(0).focus().select();
        hideMember();
    }

    function hideMember() {
        $('#modal-member').modal('hide');
    }

    function deleteData(url) {
        if (confirm('Are you sure you want to delete selected data?')) {
            $.post(url, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'delete'
                })
                .done((response) => {
                    table.ajax.reload(() => loadForm($('#discount').val()));
                })
                .fail((errors) => {
                    alert('Unable to delete data');
                    return;
                });
        }
    }

    function loadForm(discount = 0, accepted = 0) {
        $('#total').val($('.total').text());
        $('#total_item').val($('.total_item').text());

        $.get(`{{ url('/transaction/loadform') }}/${discount}/${$('.total').text()}/${accepted}`)
            .done(response => {
                $('#totalrp').val('TZs '+ response.totalrp);
                $('#payrp').val('TZs '+ response.payrp);
                $('#pay').val(response.pay);
                $('.display-pay').text('Pay: TZs '+ response.payrp);
                $('.display-spelledout').text(response.spelledout);

                $('#return').val('$'+ response.returnrp);
                if ($('#accepted').val() != 0) {
                    $('.display-pay').text('Return: TZs '+ response.returnrp);
                    $('.display-spelledout').text(response.return_spelledout);
                }
            })
            .fail(errors => {
                alert('Unable to display data');
                return;
            })
    }
</script>
@endpush