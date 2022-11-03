@if($point == 1 && $can_project_crud)
<div id="project-btn-div" style="margin-left:20px;float:left;display:none;">
    <button type="button" class="btn btn-danger" id="project-btn-delete" onclick="deleteProjectBtn();">刪除</button>
    <button type="button" class="btn btn-primary" id="project-btn-edit" onclick="editProjectBtn();">編輯</button>
    <span id="project-btn-title"></span>
</div>
@endif