<tr>
    <td>
        <input type="number"
               class="form-control mb-2 mr-sm-2 min_price"
               data-id="" name="ranges[0][min_price]"
               value="1" readonly
               min="0" required
               placeholder="{{__('Minimum Price')}}">
    </td>
    <td>
        <input type="number" step="any"
               class="form-control mb-2 mr-sm-2 max_price"
               data-id="" name="ranges[0][max_price]"
               value="" min="0" required
               placeholder="{{__('Maximum Price')}}">
    </td>
    <td>
        <input type="number"
               class="form-control mb-2 mr-sm-2"
               id="link" name="ranges[0][multiplier]"
               value="" required
               min="0" step="any"
               placeholder="{{__('Price Multiplier')}}">
    </td>
    <td>
        <button type="button" class="btn btn-icon btn-sm btn-danger remove-row"><i
                    class="bx bx-trash"></i></button>
    </td>
</tr>