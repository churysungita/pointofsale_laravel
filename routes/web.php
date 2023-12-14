<?php

use App\Http\Controllers\{
    DashboardController,
    CategoryController,
    ReportController,
    ProductController,
    MemberController,
    ExpenditureController,
    PurchaseController,
    PurchaseDetailController,
    SalesController,
    SalesDetailController,
    SettingController,
    SupplierController,
    UserController,
    

};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::group(['middleware' => 'level:1'], function () {
        Route::get('/category/data', [CategoryController::class, 'data'])->name('category.data');
        Route::resource('/category', CategoryController::class);

        Route::get('/product/data', [ProductController::class, 'data'])->name('product.data');
        Route::post('/product/delete-selected', [ProductController::class, 'deleteSelected'])->name('product.delete_selected');
        Route::post('/product/print-barcode', [ProductController::class, 'printBarcode'])->name('product.print_barcode');
        Route::resource('/product', ProductController::class);

        Route::get('/member/data', [MemberController::class, 'data'])->name('member.data');
        Route::post('/member/print-member', [MemberController::class, 'printMember'])->name('member.print_member');
        
        Route::resource('/member', MemberController::class);

        Route::get('/supplier/data', [SupplierController::class, 'data'])->name('supplier.data');
        Route::resource('/supplier', SupplierController::class);

        Route::get('/expenditure/data', [ExpenditureController::class, 'data'])->name('expenditure.data');
        Route::resource('/expenditure', ExpenditureController::class);

        Route::get('/purchase/data', [PurchaseController::class, 'data'])->name('purchase.data');
        Route::get('/purchase/{id}/create', [PurchaseController::class, 'create'])->name('purchase.create');
        Route::resource('/purchase', PurchaseController::class)
            ->except('create');

        Route::get('/purchase_details/{id}/data', [PurchaseDetailController::class, 'data'])->name('purchase_details.data');
        Route::get('/purchase_details/loadform/{discount}/{total}', [PurchaseDetailController::class, 'loadForm'])->name('purchase_details.load_form');
        Route::resource('/purchase_details', PurchaseDetailController::class)
            ->except('create', 'show', 'edit');

        Route::get('/sales/data', [SalesController::class, 'data'])->name('sales.data');
        Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
        Route::get('/sales/{id}', [SalesController::class, 'show'])->name('salessales.show');
        Route::delete('/sales/{id}', [SalesController::class, 'destroy'])->name('sales.destroy');
    });

    Route::group(['middleware' => 'level:1,2'], function () {
        Route::get('/transaction/new', [SalesController::class, 'create'])->name('transaction.new');
        Route::post('/transaction/save', [SalesController::class, 'store'])->name('transaction.save');
        Route::get('/transaction/finished', [SalesController::class, 'finished'])->name('transaction.finished');
        Route::get('/transaction/smallnotes', [SalesController::class, 'smallNote'])->name('transaction.small_notes');
        Route::get('/transaction/bignote', [SalesController::class, 'noteBig'])->name('transaction.big_note');

        Route::get('/transaction/{id}/data', [SalesDetailController::class, 'data'])->name('transaction.data');
        Route::get('/transaction/loadform/{discount}/{total}/{accepted}', [SalesDetailController::class, 'loadForm'])->name('transaction.load_form');
        Route::resource('/transaction', SalesDetailController::class)
            ->except('create', 'show', 'edit');
    });

    Route::group(['middleware' => 'level:1'], function () {
        Route::get('/reportProductController', [ReportController::class, 'index'])->name('reportProductController.index');
        Route::get('/reportProductController/data/{start}/{end}', [ReportController::class, 'data'])->name('reportProductController.data');
        Route::get('/reportProductController/pdf/{start}/{end}', [ReportController::class, 'exportPDF'])->name('reportProductController.export_pdf');

        Route::get('/user/data', [UserController::class, 'data'])->name('user.data');
        Route::resource('/user', UserController::class);

        Route::get('/setting', [SettingController::class, 'index'])->name('setting.index');
        Route::get('/setting/first', [SettingController::class, 'show'])->name('setting.show');
        Route::post('/setting', [SettingController::class, 'update'])->name('setting.update');
    });
 
    Route::group(['middleware' => 'level:1,2'], function () {
        Route::get('/profil', [UserController::class, 'profil'])->name('user.profil');
        Route::post('/profil', [UserController::class, 'updateProfil'])->name('user.update_profil');
    });
});