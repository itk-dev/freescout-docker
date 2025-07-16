@php
    if (!isset($errors)) {
        $errors = collect([]);
    }
    $values = request()->get('f');
@endphp
@if (request()->get('success') && empty($conversation->id))
    <div class="alert alert-success text-center">
        <strong>{{ __('Your message has been sent!') }}</strong>
        @if (\EndUserPortal::authCustomer() && !empty(request()->get('ticket_id')))
             <a href="{{ route('enduserportal.ticket', ['mailbox_id' => EndUserPortal::encodeMailboxId($mailbox->id), 'conversation_id' => request()->get('ticket_id')])  }}">({{ __('View') }})</a>
        @endif
    </div>
    <div class="text-center margin-bottom">
        {{-- request()->url() does not return HTTPS protocol --}}
        <a href="{{ parse_url(request()->url(), PHP_URL_PATH) }}?{{ http_build_query(array_merge(request()->all(), ['success' => '', 'message' => ''])) }}">{{ __('Submit another message') }}</a>
    </div>
@else
    @if (request()->get('success') && !empty($conversation->id))
        <div class="alert alert-success text-center">
            <strong>{{ __('Your message has been sent!') }}</strong>
        </div>
    @endif
    <form class="" method="POST" action="{{ $form_action ?? '' }}" id="eup-ticket-form">
        <div id="eup-submit-form-main-area">
            {{ csrf_field() }}
            <input type="hidden" name="conversation_id" value="{{ $conversation->id }}"/>
            {{--<input type="hidden" name="mailbox_id" value="{{ $mailbox->id }}"/>--}}
            {{--<input type="hidden" name="is_create" value="@if (empty($conversation->id)){{ '1' }}@endif"/>--}}

            {{-- Spam protection --}}
            <div class="form-group hidden">
                <input type="text" class="form-control" name="age" value="" />
            </div>

            @if (!\EndUserPortal::authCustomer())
                <div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
                    <input type="text" class="form-control eup-remember input-md" name="name" value="{{ old('name', $values['name'] ?? '') }}" placeholder="{{ __('Your Name') }}" @if (!empty($values['name'])) data-prefilled="1" @endif />

                    @include('partials/field_error', ['field'=>'customer_name'])
                </div>
            @endif

            {{--<div class="form-group {{ $errors->has('phone') ? ' has-error' : '' }}">
                <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" placeholder="{{ __('Phone Number') }}" />

                @include('partials/field_error', ['field'=>'phone'])
            </div>--}}

            @if (!\EndUserPortal::authCustomer())
                <div class="form-group {{ $errors->has('email') ? ' has-error' : '' }}">
                    <input type="email" class="form-control eup-remember input-md" name="email" value="{{ old('email', $values['email'] ?? '') }}" placeholder="{{ __('Email Address') }}*" @if (!empty($values['email'])) data-prefilled="1" @endif required autofocus />

                    @include('partials/field_error', ['field'=>'email'])
                </div>
            @endif

            @php
                if (empty($mailbox_id)) {
                    $mailbox_id = \EndUserPortal::decodeMailboxId($mailbox->id, \EndUserPortal::WIDGET_SALT);
                }
            @endphp

            @if (\Module::isActive('customfields'))
                @php

                    // We do not check request string as:
                    // - Submit another ticket button does not work in this case
                    // - In the Portal there is not request parameters.
                    // $cf = Request::get('cf');
                    // if (empty($cf) || !is_array($cf)) {
                    $widget_settings = \EndUserPortal::getWidgetSettings($mailbox_id);
                    $cf = $widget_settings['cf'] ?? [];
                    //}
                @endphp
                @if (!empty($cf) && is_array($cf))
                    @php
                        $custom_fields = \CustomField::getMailboxCustomFields($mailbox_id);
                        $add_calendar = false;
                    @endphp
                    @foreach($cf as $custom_field_id)
                        @foreach($custom_fields as $custom_field)
                            @php
                                $custom_field_value = $values['cf_'.$custom_field->id] ?? '';
                                $prefilled = false;
                            @endphp
                            @if ($custom_field->id == $custom_field_id)
                                @if ($custom_field->type == CustomField::TYPE_DATE) @php $add_calendar = true @endphp @endif
                                <div class="form-group">
                                    @if (0 === strcasecmp($custom_field->name, 'URL'))
                                        <p><strong>Indsæt webadressen hvor du oplever at have brug for support</strong></p>
                                    @endif


                                    @if ($custom_field->type == CustomField::TYPE_DROPDOWN)
                                        @foreach($custom_field->options as $option_key => $option_name)
                                            @php
                                                if ($option_key == $custom_field_value || $option_name == $custom_field_value) {
                                                    $prefilled = true;
                                                    break;
                                                }
                                            @endphp
                                        @endforeach
                                        <select class="form-control eup-remember" name="cf[{{ $custom_field->id }}]" @if ($prefilled) data-prefilled="1" @endif @if ($custom_field->required) required @endif>
                                            <option value="">{{ $custom_field->name }}@if ($custom_field->required)*@endif</option>

                                            @if (is_array($custom_field->options))
                                                @foreach($custom_field->options as $option_key => $option_name)
                                                    <option value="{{ $option_key }}" @if ($option_key == $custom_field_value || $option_name == $custom_field_value) selected @endif>{{ $option_name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    @elseif ($custom_field->type == CustomField::TYPE_MULTI_LINE)
                                        <textarea name="cf[{{ $custom_field->id }}]" class="form-control eup-remember" value="" @if ($custom_field_value) data-prefilled="1" @endif placeholder="{{ $custom_field->name }}@if ($custom_field->required)*@endif" @if ($custom_field->required) required @endif rows="3">{{ $custom_field_value }}</textarea>
                                    @elseif ($custom_field->type == CustomField::TYPE_MULTISELECT)
                                        {{-- Add hidden input to allow saving empty value --}}
                                        <input type="hidden" name="cf[{{ $custom_field->id }}][]" value="" />
                                        <select class="form-control @if (!$custom_field->value) placeholdered @endif cf-multiselect" name="cf[{{ $custom_field->id }}][]" multiple @if ($custom_field->required) required @endif placeholder="{{ $custom_field->name }}@if ($custom_field->required)*@endif">
                                            @foreach ($custom_field->getMultiselectValues() as $value)
                                                <option value="{{ $value }}" selected="selected">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    @elseif ($custom_field->type == CustomField::TYPE_DROPDOWN_MULTISELECT)
                                        {{-- Add hidden input to allow saving empty value --}}
                                        <input type="hidden" name="cf[{{ $custom_field->id }}][]" value="" />
                                        <select multiple="multiple" style="height:auto;" name="cf[{{ $custom_field->id }}][]"
                                                class="form-control @if (!$custom_field->value) placeholdered @endif cf-multiselect-dropdown"
                                                @if ($custom_field->required) required @endif placeholder="{{ $custom_field->name }}@if ($custom_field->required)*@endif">
                                        @php
                                            // Retrieve the selected values from the custom field
                                            $selectedValues = is_array($custom_field->getMultiselectValues()) ? $custom_field->getMultiselectValues() : [];
                                        @endphp
                                        @if (is_array($custom_field->options))
                                            @foreach ($custom_field->options as $option_key => $option_name)
                                                <option value="{{ $option_name }}"
                                                        {{ in_array($option_name, $selectedValues) ? 'selected' : '' }}>
                                                    {{ $option_name }}
                                                </option>
                                            @endforeach
                                        @endif
                                        </select>
                                    @else
                                        <input name="cf[{{ $custom_field->id }}]" class="form-control eup-remember @if ($custom_field->type == CustomField::TYPE_DATE) eup-type-date @endif" value="{{ $custom_field_value }}" @if ($custom_field_value) data-prefilled="1" @endif placeholder="{{ $custom_field->name }}@if ($custom_field->required)*@endif" @if ($custom_field->required) required @endif
                                            @if ($custom_field->type == CustomField::TYPE_NUMBER)
                                                type="number"
                                            @else
                                                type="text"
                                            @endif
                                        />
                                    @endif
                                    <!-- TODO: Is this the right place for the help text? -->
                                    @if (0 === strcasecmp($custom_field->name, 'URL'))
                                        <span style="color: #808080;">F.eks. https://eksempel.dk/eksempelside</span>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    @endforeach
                @endif
            @endif

            <p><strong>Kort beskrivelse af sagen</strong></p>

            @if ((int)\EndUserPortal::getMailboxParam($mailbox, 'subject') && empty($conversation->id))
                <div class="form-group{{ $errors->has('subject') ? ' has-error' : '' }}">
                    <input name="subject" class="form-control" value="{{ old('subject', $values['subject'] ?? '') }}" placeholder="{{ __('Subject') }}*" @if (!empty($values['subject'])) data-prefilled="1" @endif required type="text" />

                    @include('partials/field_error', ['field'=>'subject'])
                </div>
            @endif

            <p><strong>Tid er penge … fortæl os præcist …</strong></p>
            <ol>
                <li>Hvad du gjorde forud for at du fik brug for support?</li>
                <li>Hvad du oplever der sker?</li>
                <li>Hvad du forventede der skulle ske?</li>
            </ol>
            <p>En god fejlbeskrivelse gør det nemmere og hurtigere at finde en fejl.</p>

            {{-- Set default message if not already set --}}
            @php
            $text = <<<'EOF'
            1. Hvad gjorde du?
            Skriv dit svar her ...

            2. Hvad oplever du?
            Skriv dit svar her ...

            3. Hvad forventede du?
            Skriv dit svar her ...
            EOF;

            $placeholder = __('Message').'*';
            // @todo Should we set the placeholder?
            // See https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/textarea#placeholder for details on line breaks in textarea placeholders.
            $placeholder = $text;
            // @todo Should we help the user by prefilling the message?
            if (empty($values['message'])) {
              $values['message'] = $text;
            }
            @endphp

            <div class="form-group{{ $errors->has('message') ? ' has-error' : '' }}">

                <textarea class="form-control eup-remember" name="message" rows="13" placeholder="{{ $placeholder }}" @if (!empty($values['message'])) data-prefilled="1" @endif required autofocus>{{ old('message', $values['message'] ?? '') }}</textarea>

                @include('partials/field_error', ['field'=>'message'])

            </div>

            <div class="form-group">
                <div class="attachments-upload" id="eup-uploaded-attachments">
                    <ul></ul>
                </div>
                <div class="eup-att-dropzone">
                    <i class="glyphicon glyphicon-paperclip"></i> {{ __('Add attachments') }}
                </div>
            </div>

            @if ((int)\EndUserPortal::getMailboxParam($mailbox, 'consent'))
                <div class="form-group">
                    <label class="checkbox" for="eup_consent">
                        <input type="checkbox" value="1" id="eup_consent" required="required"> {!! __('I accept :what', ['what' => '<a href="'.route('enduserportal.ajax_html', ['mailbox_id' => EndUserPortal::encodeMailboxId($mailbox_id, \EndUserPortal::WIDGET_SALT), 'action' => 'privacy_policy']).'" data-trigger="modal" data-modal-title="'.__('Privacy Policy').'">'.__('Privacy Policy').'</a>']) !!}</a>
                    </label>
                </div>
            @endif
        </div>

        @action('enduserportal.submit_form.before_submit')

        <div id="eup-submit-form-bottom">
            <div class="form-group">
                <input type="submit" class="btn btn-block btn-primary btn-lg eup-btn-ticket-submit @action('enduserportal.submit_form.submit_class')" @action('enduserportal.submit_form.submit_attrs') {!! $submit_btn_attrs ?? '' !!} data-loading-text="@if (empty($conversation->id)){{ __('Send') }}@else{{ __('Reply') }}@endif…" value="@if (empty($conversation->id)){{ __('Send') }}@else{{ __('Reply') }}@endif"/>
            </div>
            {!! $submit_area_append ?? '' !!}
        </div>

    </form>

    @if (!empty($add_calendar))

        @section('eup_stylesheets')
            @parent
            <link href="{{ asset('js/flatpickr/flatpickr.min.css') }}" rel="stylesheet">
        @endsection

        @section('eup_javascripts')
            @parent
            {!! Minify::javascript(['/js/flatpickr/flatpickr.min.js', '/js/flatpickr/l10n/'.strtolower(Config::get('app.locale')).'.js']) !!}
        @endsection
    @endif
@endif
