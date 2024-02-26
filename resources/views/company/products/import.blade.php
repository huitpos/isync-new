{{ $errors }}

<form action="{{ route('company.products.import', ['companySlug' => $company->slug]) }}" method="post" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file">
    <button type="submit">Upload & Import</button>
</form>