<!-- Compose Email -->
<div class="app-email-compose modal" id="emailComposeSidebar" tabindex="-1"
    aria-labelledby="emailComposeSidebarLabel" aria-hidden="true">
    <div class="modal-dialog m-0 me-md-4 mb-4 modal-lg">
        <div class="modal-content p-0">
            <div class="modal-header py-3 bg-body">
                <h5 class="modal-title fs-5">Soạn Mail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body flex-grow-1 pb-sm-0 p-4 py-2">
                <form class="email-compose-form" method="post"
                    action="newsletters-send/man/{{$type}}" enctype="multipart/form-data">
                    <div class="email-compose-to d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0" for="emailContacts">Đến:</label>
                        <div class="select2-primary border-0 shadow-none flex-grow-1 mx-2">
                            <select name="listemail[]" class="select2 select-email-contacts form-select"
                                id="emailContacts" multiple>
                                @foreach ($items as $value)
                                    <option data-avatar="user.png" value="{{ $value['id'] }}">
                                        {{ $value['fullname'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="email-compose-toggle-wrapper">
                            <a class="email-compose-toggle-cc" href="javascript:void(0);">Cc |</a>
                            <a class="email-compose-toggle-bcc" href="javascript:void(0);">Bcc</a>
                        </div>
                    </div>

                    <div class="email-compose-cc d-none">
                        <hr class="container-m-nx my-2" />
                        <div class="d-flex align-items-center">
                            <label for="email-cc" class="form-label mb-0">Cc: </label>
                            <input type="text" class="form-control border-0 shadow-none flex-grow-1 mx-2"
                                id="email-cc" name="cc" placeholder="someone@email.com" />
                        </div>
                    </div>
                    <div class="email-compose-bcc d-none">
                        <hr class="container-m-nx my-2" />
                        <div class="d-flex align-items-center">
                            <label for="email-bcc" class="form-label mb-0">Bcc: </label>
                            <input type="text" class="form-control border-0 shadow-none flex-grow-1 mx-2"
                                id="email-bcc" name="bcc" placeholder="someone@email.com" />
                        </div>
                    </div>
                    <hr class="container-m-nx my-2" />
                    <div class="email-compose-subject d-flex align-items-center mb-2">
                        <label for="email-subject" class="email-subject form-label mb-0">Chủ để:</label>
                        <input type="text" name="subject"
                            class="form-control border-0 shadow-none flex-grow-1 mx-2" id="email-subject"
                            placeholder="Chủ đề" />
                    </div>
                    <hr class="container-m-nx my-2">
                    <div class="email-compose-message container-m-nx p-2">
                        <textarea class="form-control " name="content" id="content" rows="5"  placeholder="Nội dung thông tin"></textarea>
                    </div>
                    <hr class="container-m-nx mt-0 mb-2" />
                    <div
                        class="email-compose-actions d-flex justify-content-between align-items-center mt-3 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <i class="ti ti-send ti-xs me-1"></i>Gửi
                                </button>
                            </div>
                            <label for="attach-file"><i
                                    class="ti ti-paperclip cursor-pointer ms-2"></i></label>
                            <input type="file" name="file" class="d-none" id="attach-file" />
                        </div>
                        <div class="d-flex align-items-center">
                            <button type="reset" class="btn" data-bs-dismiss="modal"
                                aria-label="Close">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </div>
                    <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Compose Email -->
