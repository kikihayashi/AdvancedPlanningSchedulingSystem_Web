<table class="table project-table inputForm" style="margin-bottom:0px;">
    <tbody>
        <tr>
            <td colspan="4"><span class="with-line" style="--width: 35%;"></span></td>
        </tr>
        <tr>
            <td style="width:30%">
                <div style="display:flex;float:right;align-items: center;">
                    <div class="removeLot"><a href="#">&#10005;</a></div>
                    &emsp;<span style="color:red">&#42;</span>&ensp;Lot No :
                </div>
            </td>
            <td style="width:20%">
                <input required min="1" type="number" name="lot_no[]"
                    class="checkLot input-small" />
            </td>
            <td class="right" style="width:11%"><span style="color:red">&#42;</span>&ensp;台數 :</td>
            <td style="width:39%">
                <input required min="0" type="number" name="number[]" id="number"
                    class="input-small" />
            </td>
        </tr>
        <tr>
            <td class="right" colspan="1" id="remark-title">備註 : </td>
            <td colspan="3">
                <textarea class="textarea-large" name="remark[]" id="remark" placeholder="說明"></textarea>
            </td>
        </tr>
    </tbody>
</table>