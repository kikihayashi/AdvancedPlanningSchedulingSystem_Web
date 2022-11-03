<div class="left-sidebar-div">
    <div class="left-content-div">
        <nav class="menu-nav">
            <ul class="menu-list">
                <!-- 選項 -->
                <li class="menu-item" data-toggle="tooltip" title="首頁">
                    <div class="menu-item-inner">
                        <a href="{{route('home')}}" id="home" class="fa fa-home" style="transition:none"></a>
                    </div>
                </li>
                <li class="menu-item" data-toggle="tooltip" title="管理">
                    <div class="menu-item-inner">
                        <a href="{{route('UserController.showUserPage')}}" id="control" class="fa fa-wrench"
                            style="transition:none"></a>
                    </div>
                </li>
                <li class="menu-item" data-toggle="tooltip" title="大計劃管理系統">
                    <div class="menu-item-inner">
                        <a href="{{route('ParameterController.showParameterPage')}}" id="system" class="fa fa-cog"
                            style="transition:none"></a>
                    </div>
                </li>
            </ul>
        </nav>
    </div>
</div>