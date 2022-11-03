 @php
 $projectName = '';
 switch($projectType) {
 case 'M':
 $projectName='大計劃列表';
 break;
 case 'PY':
 $projectName='年度生產計畫';
 break;
 case 'SY':
 $projectName='年度出荷計畫';
 break;
 case 'PM':
 $projectName='月度生產計畫';
 break;
 case 'SM':
 $projectName='月度出荷計畫';
 break;
 }
 @endphp
 <!-- 計畫列表的TAB -->
 <li class="nav-item">
     <a id="nav-link-a" class="nav-link {{($tableData['selectTab']=='projectList')? 'active':''}}" data-toggle="tab"
         href="#projectList-page" onclick="selectTab('projectList');">{{$projectName}}</a>
 </li>
 <!-- 新增的TAB -->
 @if($point == 1 && $can_project_crud)
 @if($projectType=='SM')
 <li class="nav-item">
     <a id="nav-link-a" class="nav-link {{($tableData['selectTab']=='projectItemCode')? 'active':''}}" data-toggle="tab"
         href="#projectItemCode-page" onclick="selectTab('projectItemCode');">新增機種</a>
 </li>
 <li class="nav-item">
     <a id="nav-link-a" class="nav-link {{($tableData['selectTab']=='projectTransport')? 'active':''}}" data-toggle="tab"
         href="#projectTransport-page" onclick="selectTab('projectTransport');">新增出荷計畫</a>
 </li>
 @else
 <li class="nav-item">
     <a id="nav-link-a" class="nav-link {{($tableData['selectTab']=='projectCreate')? 'active':''}}" data-toggle="tab"
         href="#projectCreate-page" onclick="selectTab('projectCreate');">新增</a>
 </li>
 @endif
 @elseif($point == 0 &&  $can_project_crud && ($projectType=='PY' || $projectType=='PM'))
 <li class="nav-item">
     <a id="nav-link-a" class="nav-link {{($tableData['selectTab']=='projectSap')? 'active':''}}" data-toggle="tab"
         href="#projectSap-page" onclick="selectTab('projectSap');">轉入SAP</a>
 </li>
 @endif