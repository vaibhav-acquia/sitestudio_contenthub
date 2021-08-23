<?php

namespace Drupal\Tests\sitestudio_contenthub_publisher\Unit\EventSubscriber\Cdf;

use Acquia\ContentHubClient\CDF\CDFObject;
use Drupal\acquia_contenthub\Event\SerializeAdditionalMetadataEvent;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\sitestudio_contenthub_publisher\EventSubscriber\Cdf\CohesionLayoutSerializationHandler;

/**
 * Tests for handling CohesionLayout entities serialization.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_contenthub_publisher\Unit\EventSubscriber\Cdf
 *
 * @covers \Drupal\sitestudio_contenthub_publisher\EventSubscriber\Cdf\CohesionLayoutSerializationHandler::onSerializeCohesionLayout
 */
class CohesionLayoutSerializationHandlerTest extends UnitTestCase {

  const NODE_UUIDS = [
    '3' => 'a48e537a-6ec9-4191-b518-ed5e6d559a12',
    '4' => 'f8c3512b-aa9e-4008-8bf3-c03193ca3afa',
    '56' => 'ae85e706-809b-4c6f-9ef5-0a0f6779d6c8',
    '64' => 'dd871855-28df-475a-9d93-6cb4cba62d27',
  ];
  const LAYOUT_CANVAS_UUID = '3a68e76a-d224-4fff-9737-a13cfa481165';
  const ENTITY_TYPE = 'drupal8_content_entity';
  const JSON_VALUES = '{"canvas":[{"uid":"cpt_test_component","type":"component","title":"Test Component","enabled":true,"category":"category-10","componentId":"cpt_test_component","componentType":"link","uuid":"d23a59d5-c8d7-49b8-9cda-61fa01aadf55","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_test_component","type":"component","title":"Test Component","enabled":true,"category":"category-10","componentId":"cpt_test_component","componentType":"link","uuid":"19c96fe4-db43-4f2f-8c91-8081b7a948fb","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_test_component","type":"component","title":"Test Component","enabled":true,"category":"category-10","componentId":"cpt_test_component","componentType":"link","uuid":"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_links_pattern_repeater","type":"component","title":"Links pattern repeater","enabled":true,"category":"category-4","componentId":"cpt_links_pattern_repeater","componentType":"component-pattern-repeater","uuid":"e7d1a1ac-abef-4971-9273-9d25bcc8db79","parentUid":"root","isContainer":0,"children":[]}],"mapper":{},"model":{"d23a59d5-c8d7-49b8-9cda-61fa01aadf55":{"settings":{"title":"Test Component"},"4793e582-fe13-490c-99d2-badcce843df7":"node::64"},"19c96fe4-db43-4f2f-8c91-8081b7a948fb":{"settings":{"title":"Test Component"},"4793e582-fe13-490c-99d2-badcce843df7":"node::56"},"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c":{"settings":{"title":"Test Component"},"4793e582-fe13-490c-99d2-badcce843df7":"https:\/\/www.drupal.org\/"},"e7d1a1ac-abef-4971-9273-9d25bcc8db79":{"settings":{"title":"Links pattern repeater"},"d1653d6c-68ef-4b15-aabb-fdf3daf5064e":[{"f1a9f274-f030-47d5-a831-79a23da8a59e":"External","abdc6059-1b0d-498e-ac6a-daa58cbb85fb":"https:\/\/www.drupal.org\/"},{"f1a9f274-f030-47d5-a831-79a23da8a59e":"Node 1","abdc6059-1b0d-498e-ac6a-daa58cbb85fb":"node::4"},{"f1a9f274-f030-47d5-a831-79a23da8a59e":"Node 2","abdc6059-1b0d-498e-ac6a-daa58cbb85fb":"node::3"}]}},"previewModel":{"d23a59d5-c8d7-49b8-9cda-61fa01aadf55":{},"19c96fe4-db43-4f2f-8c91-8081b7a948fb":{},"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c":{},"e7d1a1ac-abef-4971-9273-9d25bcc8db79":{}},"variableFields":{"d23a59d5-c8d7-49b8-9cda-61fa01aadf55":[],"19c96fe4-db43-4f2f-8c91-8081b7a948fb":[],"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c":[],"e7d1a1ac-abef-4971-9273-9d25bcc8db79":[]},"meta":{"fieldHistory":[]}}';
  const LAYOUT_CANVAS_METADATA = [
    "metadata" => [
      "default_language" => "en",
      "dependencies" => [
        "module" => [
          "cohesion",
        ],
      ],
      "field" => [
        "uuid" => [
          "type" => "uuid",
        ],
        "type" => [
          "type" => "entity_reference",
          "target" => "node_type",
        ],
        "parent_id" => [
          "type" => "string",
        ],
        "parent_type" => [
          "type" => "string",
        ],
        "parent_field_name" => [
          "type" => "string",
        ],
        "last_entity_update" => [
          "type" => "string",
        ],
        "langcode" => [
          "type" => "language",
        ],
        "template" => [
          "type" => "string_long",
        ],
        "json_values" => [
          "type" => "string_long",
        ],
        "styles" => [
          "type" => "string_long",
        ],
        "default_langcode" => [
          "type" => "boolean",
        ],
        "revision_default" => [
          "type" => "boolean",
        ],
        "revision_translation_affected" => [
          "type" => "boolean",
        ],
      ],
      "data" => "eyJ1dWlkIjp7InZhbHVlIjp7ImVuIjp7InZhbHVlIjoiMjJkNWU5NDYtYTIxNC00MzcxLTlmNDUtNTI5YmRiNzY1OTg3In19fSwianNvbl92YWx1ZXMiOnsidmFsdWUiOnsiZW4iOnsidmFsdWUiOiJ7XCJjYW52YXNcIjpbe1widWlkXCI6XCJjcHRfdGVzdF9jb21wb25lbnRcIixcInR5cGVcIjpcImNvbXBvbmVudFwiLFwidGl0bGVcIjpcIlRlc3QgQ29tcG9uZW50XCIsXCJlbmFibGVkXCI6dHJ1ZSxcImNhdGVnb3J5XCI6XCJjYXRlZ29yeS0xMFwiLFwiY29tcG9uZW50SWRcIjpcImNwdF90ZXN0X2NvbXBvbmVudFwiLFwiY29tcG9uZW50VHlwZVwiOlwibGlua1wiLFwidXVpZFwiOlwiZDIzYTU5ZDUtYzhkNy00OWI4LTljZGEtNjFmYTAxYWFkZjU1XCIsXCJwYXJlbnRVaWRcIjpcInJvb3RcIixcImlzQ29udGFpbmVyXCI6MCxcImNoaWxkcmVuXCI6W119LHtcInVpZFwiOlwiY3B0X3Rlc3RfY29tcG9uZW50XCIsXCJ0eXBlXCI6XCJjb21wb25lbnRcIixcInRpdGxlXCI6XCJUZXN0IENvbXBvbmVudFwiLFwiZW5hYmxlZFwiOnRydWUsXCJjYXRlZ29yeVwiOlwiY2F0ZWdvcnktMTBcIixcImNvbXBvbmVudElkXCI6XCJjcHRfdGVzdF9jb21wb25lbnRcIixcImNvbXBvbmVudFR5cGVcIjpcImxpbmtcIixcInV1aWRcIjpcIjE5Yzk2ZmU0LWRiNDMtNGYyZi04YzkxLTgwODFiN2E5NDhmYlwiLFwicGFyZW50VWlkXCI6XCJyb290XCIsXCJpc0NvbnRhaW5lclwiOjAsXCJjaGlsZHJlblwiOltdfSx7XCJ1aWRcIjpcImNwdF90ZXN0X2NvbXBvbmVudFwiLFwidHlwZVwiOlwiY29tcG9uZW50XCIsXCJ0aXRsZVwiOlwiVGVzdCBDb21wb25lbnRcIixcImVuYWJsZWRcIjp0cnVlLFwiY2F0ZWdvcnlcIjpcImNhdGVnb3J5LTEwXCIsXCJjb21wb25lbnRJZFwiOlwiY3B0X3Rlc3RfY29tcG9uZW50XCIsXCJjb21wb25lbnRUeXBlXCI6XCJsaW5rXCIsXCJ1dWlkXCI6XCJlYmFmYTZhOS1iMGI4LTQ5Y2ItYTQ4MS1kOGMxMjNjNTJlMWNcIixcInBhcmVudFVpZFwiOlwicm9vdFwiLFwiaXNDb250YWluZXJcIjowLFwiY2hpbGRyZW5cIjpbXX0se1widWlkXCI6XCJjcHRfbGlua3NfcGF0dGVybl9yZXBlYXRlclwiLFwidHlwZVwiOlwiY29tcG9uZW50XCIsXCJ0aXRsZVwiOlwiTGlua3MgcGF0dGVybiByZXBlYXRlclwiLFwiZW5hYmxlZFwiOnRydWUsXCJjYXRlZ29yeVwiOlwiY2F0ZWdvcnktNFwiLFwiY29tcG9uZW50SWRcIjpcImNwdF9saW5rc19wYXR0ZXJuX3JlcGVhdGVyXCIsXCJjb21wb25lbnRUeXBlXCI6XCJjb21wb25lbnQtcGF0dGVybi1yZXBlYXRlclwiLFwidXVpZFwiOlwiZTdkMWExYWMtYWJlZi00OTcxLTkyNzMtOWQyNWJjYzhkYjc5XCIsXCJwYXJlbnRVaWRcIjpcInJvb3RcIixcImlzQ29udGFpbmVyXCI6MCxcImNoaWxkcmVuXCI6W119XSxcIm1hcHBlclwiOnt9LFwibW9kZWxcIjp7XCJkMjNhNTlkNS1jOGQ3LTQ5YjgtOWNkYS02MWZhMDFhYWRmNTVcIjp7XCJzZXR0aW5nc1wiOntcInRpdGxlXCI6XCJUZXN0IENvbXBvbmVudFwifSxcIjQ3OTNlNTgyLWZlMTMtNDkwYy05OWQyLWJhZGNjZTg0M2RmN1wiOlwibm9kZTo6NjRcIn0sXCIxOWM5NmZlNC1kYjQzLTRmMmYtOGM5MS04MDgxYjdhOTQ4ZmJcIjp7XCJzZXR0aW5nc1wiOntcInRpdGxlXCI6XCJUZXN0IENvbXBvbmVudFwifSxcIjQ3OTNlNTgyLWZlMTMtNDkwYy05OWQyLWJhZGNjZTg0M2RmN1wiOlwibm9kZTo6NTZcIn0sXCJlYmFmYTZhOS1iMGI4LTQ5Y2ItYTQ4MS1kOGMxMjNjNTJlMWNcIjp7XCJzZXR0aW5nc1wiOntcInRpdGxlXCI6XCJUZXN0IENvbXBvbmVudFwifSxcIjQ3OTNlNTgyLWZlMTMtNDkwYy05OWQyLWJhZGNjZTg0M2RmN1wiOlwiaHR0cHM6XFxcL1xcXC93d3cuZHJ1cGFsLm9yZ1xcXC9cIn0sXCJlN2QxYTFhYy1hYmVmLTQ5NzEtOTI3My05ZDI1YmNjOGRiNzlcIjp7XCJzZXR0aW5nc1wiOntcInRpdGxlXCI6XCJMaW5rcyBwYXR0ZXJuIHJlcGVhdGVyXCJ9LFwiZDE2NTNkNmMtNjhlZi00YjE1LWFhYmItZmRmM2RhZjUwNjRlXCI6W3tcImYxYTlmMjc0LWYwMzAtNDdkNS1hODMxLTc5YTIzZGE4YTU5ZVwiOlwiRXh0ZXJuYWxcIixcImFiZGM2MDU5LTFiMGQtNDk4ZS1hYzZhLWRhYTU4Y2JiODVmYlwiOlwiaHR0cHM6XFxcL1xcXC93d3cuZHJ1cGFsLm9yZ1xcXC9cIn0se1wiZjFhOWYyNzQtZjAzMC00N2Q1LWE4MzEtNzlhMjNkYThhNTllXCI6XCJOb2RlIDFcIixcImFiZGM2MDU5LTFiMGQtNDk4ZS1hYzZhLWRhYTU4Y2JiODVmYlwiOlwibm9kZTo6NFwifSx7XCJmMWE5ZjI3NC1mMDMwLTQ3ZDUtYTgzMS03OWEyM2RhOGE1OWVcIjpcIk5vZGUgMlwiLFwiYWJkYzYwNTktMWIwZC00OThlLWFjNmEtZGFhNThjYmI4NWZiXCI6XCJub2RlOjozXCJ9XX19LFwicHJldmlld01vZGVsXCI6e1wiZDIzYTU5ZDUtYzhkNy00OWI4LTljZGEtNjFmYTAxYWFkZjU1XCI6e30sXCIxOWM5NmZlNC1kYjQzLTRmMmYtOGM5MS04MDgxYjdhOTQ4ZmJcIjp7fSxcImViYWZhNmE5LWIwYjgtNDljYi1hNDgxLWQ4YzEyM2M1MmUxY1wiOnt9LFwiZTdkMWExYWMtYWJlZi00OTcxLTkyNzMtOWQyNWJjYzhkYjc5XCI6e319LFwidmFyaWFibGVGaWVsZHNcIjp7XCJkMjNhNTlkNS1jOGQ3LTQ5YjgtOWNkYS02MWZhMDFhYWRmNTVcIjpbXSxcIjE5Yzk2ZmU0LWRiNDMtNGYyZi04YzkxLTgwODFiN2E5NDhmYlwiOltdLFwiZWJhZmE2YTktYjBiOC00OWNiLWE0ODEtZDhjMTIzYzUyZTFjXCI6W10sXCJlN2QxYTFhYy1hYmVmLTQ5NzEtOTI3My05ZDI1YmNjOGRiNzlcIjpbXX0sXCJtZXRhXCI6e1wiZmllbGRIaXN0b3J5XCI6W119fSJ9fX0sInN0eWxlcyI6eyJ2YWx1ZSI6eyJlbiI6eyJ2YWx1ZSI6IltdIn19fSwidGVtcGxhdGUiOnsidmFsdWUiOnsiZW4iOnsidmFsdWUiOiJ7XCJjb2hlc2lvbl90aGVtZVwiOlwie1xcdTAwMjJtZXRhZGF0YVxcdTAwMjI6e1xcdTAwMjJjb250ZXh0c1xcdTAwMjI6W119LFxcdTAwMjJ0d2lnXFx1MDAyMjpcXHUwMDIye3sgYXR0YWNoX2xpYnJhcnkoXFx1MDAyN2NvaGVzaW9uXFxcXFxcXC9nbG9iYWxfbGlicmFyaWVzLnJlc3BvbnNpdmVKc1xcdTAwMjcpIH19IHt7IGF0dGFjaF9saWJyYXJ5KFxcdTAwMjdjb2hlc2lvblxcXFxcXFwvZ2xvYmFsX2xpYnJhcmllcy5tYXRjaEhlaWdodFxcdTAwMjcpIH19IHt7IGF0dGFjaF9saWJyYXJ5KFxcdTAwMjdjb2hlc2lvblxcXFxcXFwvZ2xvYmFsX2xpYnJhcmllcy5jb2hNYXRjaEhlaWdodHNcXHUwMDI3KSB9fSB7eyBhdHRhY2hfbGlicmFyeShcXHUwMDI3Y29oZXNpb25cXFxcXFxcL2dsb2JhbF9saWJyYXJpZXMud2luZG93c2Nyb2xsXFx1MDAyNykgfX0ge3sgYXR0YWNoX2xpYnJhcnkoXFx1MDAyN2NvaGVzaW9uXFxcXFxcXC9lbGVtZW50X3RlbXBsYXRlcy5saW5rXFx1MDAyNykgfX0ge3sgYXR0YWNoX2xpYnJhcnkoXFx1MDAyN2NvaGVzaW9uXFxcXFxcXC9nbG9iYWxfbGlicmFyaWVzLnBhcmFsbGF4X3Njcm9sbGluZ1xcdTAwMjcpIH19IHslIHNldCBjb21wb25lbnRfdmFyaWFibGVfNDc5M2U1ODJfZmUxM180OTBjXzk5ZDJfYmFkY2NlODQzZGY3ICV9bm9kZTo6NjR7JSBlbmRzZXQgJX0ge3sgcmVuZGVyQ29tcG9uZW50KFxcXFxcXHUwMDIyY3B0X3Rlc3RfY29tcG9uZW50XFxcXFxcdTAwMjIsIGZhbHNlLCBfY29udGV4dCwge1xcXFxcXHUwMDIyNDc5M2U1ODItZmUxMy00OTBjLTk5ZDItYmFkY2NlODQzZGY3XFxcXFxcdTAwMjI6XFxcXFxcdTAwMjJjb21wb25lbnRfdmFyaWFibGVfNDc5M2U1ODJfZmUxM180OTBjXzk5ZDJfYmFkY2NlODQzZGY3XFxcXFxcdTAwMjJ9LCBcXFxcXFx1MDAyMmQyM2E1OWQ1LWM4ZDctNDliOC05Y2RhLTYxZmEwMWFhZGY1NVxcXFxcXHUwMDIyLCBcXFxcXFx1MDAyMlxcXFxcXHUwMDIyKSAgfX0geyUgc2V0IGNvbXBvbmVudF92YXJpYWJsZV80NzkzZTU4Ml9mZTEzXzQ5MGNfOTlkMl9iYWRjY2U4NDNkZjcgJX1ub2RlOjo1NnslIGVuZHNldCAlfSB7eyByZW5kZXJDb21wb25lbnQoXFxcXFxcdTAwMjJjcHRfdGVzdF9jb21wb25lbnRcXFxcXFx1MDAyMiwgZmFsc2UsIF9jb250ZXh0LCB7XFxcXFxcdTAwMjI0NzkzZTU4Mi1mZTEzLTQ5MGMtOTlkMi1iYWRjY2U4NDNkZjdcXFxcXFx1MDAyMjpcXFxcXFx1MDAyMmNvbXBvbmVudF92YXJpYWJsZV80NzkzZTU4Ml9mZTEzXzQ5MGNfOTlkMl9iYWRjY2U4NDNkZjdcXFxcXFx1MDAyMn0sIFxcXFxcXHUwMDIyMTljOTZmZTQtZGI0My00ZjJmLThjOTEtODA4MWI3YTk0OGZiXFxcXFxcdTAwMjIsIFxcXFxcXHUwMDIyXFxcXFxcdTAwMjIpICB9fSB7JSBzZXQgY29tcG9uZW50X3ZhcmlhYmxlXzQ3OTNlNTgyX2ZlMTNfNDkwY185OWQyX2JhZGNjZTg0M2RmNyAlfWh0dHBzOlxcXFxcXFwvXFxcXFxcXC93d3cuZHJ1cGFsLm9yZ1xcXFxcXFwveyUgZW5kc2V0ICV9IHt7IHJlbmRlckNvbXBvbmVudChcXFxcXFx1MDAyMmNwdF90ZXN0X2NvbXBvbmVudFxcXFxcXHUwMDIyLCBmYWxzZSwgX2NvbnRleHQsIHtcXFxcXFx1MDAyMjQ3OTNlNTgyLWZlMTMtNDkwYy05OWQyLWJhZGNjZTg0M2RmN1xcXFxcXHUwMDIyOlxcXFxcXHUwMDIyY29tcG9uZW50X3ZhcmlhYmxlXzQ3OTNlNTgyX2ZlMTNfNDkwY185OWQyX2JhZGNjZTg0M2RmN1xcXFxcXHUwMDIyfSwgXFxcXFxcdTAwMjJlYmFmYTZhOS1iMGI4LTQ5Y2ItYTQ4MS1kOGMxMjNjNTJlMWNcXFxcXFx1MDAyMiwgXFxcXFxcdTAwMjJcXFxcXFx1MDAyMikgIH19IHslIHNldCBjb21wb25lbnRfdmFyaWFibGVfZDE2NTNkNmNfNjhlZl80YjE1X2FhYmJfZmRmM2RhZjUwNjRlXzBfZjFhOWYyNzRfZjAzMF80N2Q1X2E4MzFfNzlhMjNkYThhNTllICV9RXh0ZXJuYWx7JSBlbmRzZXQgJX0geyUgc2V0IGNvbXBvbmVudF92YXJpYWJsZV9kMTY1M2Q2Y182OGVmXzRiMTVfYWFiYl9mZGYzZGFmNTA2NGVfMF9hYmRjNjA1OV8xYjBkXzQ5OGVfYWM2YV9kYWE1OGNiYjg1ZmIgJX1odHRwczpcXFxcXFxcL1xcXFxcXFwvd3d3LmRydXBhbC5vcmdcXFxcXFxcL3slIGVuZHNldCAlfSB7JSBzZXQgY29tcG9uZW50X3ZhcmlhYmxlX2QxNjUzZDZjXzY4ZWZfNGIxNV9hYWJiX2ZkZjNkYWY1MDY0ZV8xX2YxYTlmMjc0X2YwMzBfNDdkNV9hODMxXzc5YTIzZGE4YTU5ZSAlfU5vZGUgMXslIGVuZHNldCAlfSB7JSBzZXQgY29tcG9uZW50X3ZhcmlhYmxlX2QxNjUzZDZjXzY4ZWZfNGIxNV9hYWJiX2ZkZjNkYWY1MDY0ZV8xX2FiZGM2MDU5XzFiMGRfNDk4ZV9hYzZhX2RhYTU4Y2JiODVmYiAlfW5vZGU6OjR7JSBlbmRzZXQgJX0geyUgc2V0IGNvbXBvbmVudF92YXJpYWJsZV9kMTY1M2Q2Y182OGVmXzRiMTVfYWFiYl9mZGYzZGFmNTA2NGVfMl9mMWE5ZjI3NF9mMDMwXzQ3ZDVfYTgzMV83OWEyM2RhOGE1OWUgJX1Ob2RlIDJ7JSBlbmRzZXQgJX0geyUgc2V0IGNvbXBvbmVudF92YXJpYWJsZV9kMTY1M2Q2Y182OGVmXzRiMTVfYWFiYl9mZGYzZGFmNTA2NGVfMl9hYmRjNjA1OV8xYjBkXzQ5OGVfYWM2YV9kYWE1OGNiYjg1ZmIgJX1ub2RlOjozeyUgZW5kc2V0ICV9IHt7IHJlbmRlckNvbXBvbmVudChcXFxcXFx1MDAyMmNwdF9saW5rc19wYXR0ZXJuX3JlcGVhdGVyXFxcXFxcdTAwMjIsIGZhbHNlLCBfY29udGV4dCwge1xcXFxcXHUwMDIyZDE2NTNkNmMtNjhlZi00YjE1LWFhYmItZmRmM2RhZjUwNjRlXFxcXFxcdTAwMjI6W3tcXFxcXFx1MDAyMmYxYTlmMjc0LWYwMzAtNDdkNS1hODMxLTc5YTIzZGE4YTU5ZVxcXFxcXHUwMDIyOlxcXFxcXHUwMDIyY29tcG9uZW50X3ZhcmlhYmxlX2QxNjUzZDZjXzY4ZWZfNGIxNV9hYWJiX2ZkZjNkYWY1MDY0ZV8wX2YxYTlmMjc0X2YwMzBfNDdkNV9hODMxXzc5YTIzZGE4YTU5ZVxcXFxcXHUwMDIyLFxcXFxcXHUwMDIyYWJkYzYwNTktMWIwZC00OThlLWFjNmEtZGFhNThjYmI4NWZiXFxcXFxcdTAwMjI6XFxcXFxcdTAwMjJjb21wb25lbnRfdmFyaWFibGVfZDE2NTNkNmNfNjhlZl80YjE1X2FhYmJfZmRmM2RhZjUwNjRlXzBfYWJkYzYwNTlfMWIwZF80OThlX2FjNmFfZGFhNThjYmI4NWZiXFxcXFxcdTAwMjJ9LHtcXFxcXFx1MDAyMmYxYTlmMjc0LWYwMzAtNDdkNS1hODMxLTc5YTIzZGE4YTU5ZVxcXFxcXHUwMDIyOlxcXFxcXHUwMDIyY29tcG9uZW50X3ZhcmlhYmxlX2QxNjUzZDZjXzY4ZWZfNGIxNV9hYWJiX2ZkZjNkYWY1MDY0ZV8xX2YxYTlmMjc0X2YwMzBfNDdkNV9hODMxXzc5YTIzZGE4YTU5ZVxcXFxcXHUwMDIyLFxcXFxcXHUwMDIyYWJkYzYwNTktMWIwZC00OThlLWFjNmEtZGFhNThjYmI4NWZiXFxcXFxcdTAwMjI6XFxcXFxcdTAwMjJjb21wb25lbnRfdmFyaWFibGVfZDE2NTNkNmNfNjhlZl80YjE1X2FhYmJfZmRmM2RhZjUwNjRlXzFfYWJkYzYwNTlfMWIwZF80OThlX2FjNmFfZGFhNThjYmI4NWZiXFxcXFxcdTAwMjJ9LHtcXFxcXFx1MDAyMmYxYTlmMjc0LWYwMzAtNDdkNS1hODMxLTc5YTIzZGE4YTU5ZVxcXFxcXHUwMDIyOlxcXFxcXHUwMDIyY29tcG9uZW50X3ZhcmlhYmxlX2QxNjUzZDZjXzY4ZWZfNGIxNV9hYWJiX2ZkZjNkYWY1MDY0ZV8yX2YxYTlmMjc0X2YwMzBfNDdkNV9hODMxXzc5YTIzZGE4YTU5ZVxcXFxcXHUwMDIyLFxcXFxcXHUwMDIyYWJkYzYwNTktMWIwZC00OThlLWFjNmEtZGFhNThjYmI4NWZiXFxcXFxcdTAwMjI6XFxcXFxcdTAwMjJjb21wb25lbnRfdmFyaWFibGVfZDE2NTNkNmNfNjhlZl80YjE1X2FhYmJfZmRmM2RhZjUwNjRlXzJfYWJkYzYwNTlfMWIwZF80OThlX2FjNmFfZGFhNThjYmI4NWZiXFxcXFxcdTAwMjJ9XX0sIFxcXFxcXHUwMDIyZTdkMWExYWMtYWJlZi00OTcxLTkyNzMtOWQyNWJjYzhkYjc5XFxcXFxcdTAwMjIsIFxcXFxcXHUwMDIyXFxcXFxcdTAwMjIpICB9fSBcXFxcbnslIGlmIGNvbnRlbnQgaXMgZGVmaW5lZCAlfXslIHNldCBjYXRjaF9jYWNoZSA9IGNvbnRlbnR8cmVuZGVyICV9eyUgZW5kaWYgJX1cXHUwMDIyfVwifSJ9fX0sInBhcmVudF9pZCI6eyJ2YWx1ZSI6eyJlbiI6IjY1In19LCJwYXJlbnRfdHlwZSI6eyJ2YWx1ZSI6eyJlbiI6Im5vZGUifX0sInBhcmVudF9maWVsZF9uYW1lIjp7InZhbHVlIjp7ImVuIjoiZmllbGRfbGF5b3V0X2NhbnZhcyJ9fSwibGFzdF9lbnRpdHlfdXBkYXRlIjp7InZhbHVlIjp7ImVuIjoiZW50aXR5dXBkYXRlXzAwMzIifX0sImRlZmF1bHRfbGFuZ2NvZGUiOnsidmFsdWUiOnsiZW4iOiIxIn19LCJyZXZpc2lvbl9kZWZhdWx0Ijp7InZhbHVlIjp7ImVuIjoiMSJ9fSwicmV2aXNpb25fdHJhbnNsYXRpb25fYWZmZWN0ZWQiOnsidmFsdWUiOnsiZW4iOiIxIn19LCJzY2hlZHVsZWRfdHJhbnNpdGlvbl9kYXRlIjp7InZhbHVlIjp7ImVuIjpbXX19LCJzY2hlZHVsZWRfdHJhbnNpdGlvbl9zdGF0ZSI6eyJ2YWx1ZSI6eyJlbiI6W119fX0=",
      "languages" => [
        "en",
      ],
      "version" => 2,
    ],
  ];
  const LINKS = [
    "d23a59d5-c8d7-49b8-9cda-61fa01aadf55" => [
      [
        "entity_type" => "node",
        "entity_id" => "64",
        "path" => [
          "4793e582-fe13-490c-99d2-badcce843df7",
        ],
      ],
    ],
    "19c96fe4-db43-4f2f-8c91-8081b7a948fb" => [
      [
        "entity_type" => "node",
        "entity_id" => "56",
        "path" => [
          "4793e582-fe13-490c-99d2-badcce843df7",
        ],
      ],
    ],
    "e7d1a1ac-abef-4971-9273-9d25bcc8db79" => [
      [
        "entity_type" => "node",
        "entity_id" => "4",
        "path" => [
          "d1653d6c-68ef-4b15-aabb-fdf3daf5064e",
          1,
          "abdc6059-1b0d-498e-ac6a-daa58cbb85fb",
        ],
      ],
      [
        "entity_type" => "node",
        "entity_id" => "3",
        "path" => [
          "d1653d6c-68ef-4b15-aabb-fdf3daf5064e",
          2,
          "abdc6059-1b0d-498e-ac6a-daa58cbb85fb",
        ],
      ],
    ],
  ];
  const LINKS_METADATA = [
    "d23a59d5-c8d7-49b8-9cda-61fa01aadf55" => [
      [
        "entity_type" => "node",
        "entity_id" => "dd871855-28df-475a-9d93-6cb4cba62d27",
        "path" => [
          "4793e582-fe13-490c-99d2-badcce843df7",
        ],
      ],
    ],
    "19c96fe4-db43-4f2f-8c91-8081b7a948fb" => [
      [
        "entity_type" => "node",
        "entity_id" => "ae85e706-809b-4c6f-9ef5-0a0f6779d6c8",
        "path" => [
          "4793e582-fe13-490c-99d2-badcce843df7",
        ],
      ],
    ],
    "e7d1a1ac-abef-4971-9273-9d25bcc8db79" => [
      [
        "entity_type" => "node",
        "entity_id" => "f8c3512b-aa9e-4008-8bf3-c03193ca3afa",
        "path" => [
          "d1653d6c-68ef-4b15-aabb-fdf3daf5064e",
          1,
          "abdc6059-1b0d-498e-ac6a-daa58cbb85fb",
        ],
      ],
      [
        "entity_type" => "node",
        "entity_id" => "a48e537a-6ec9-4191-b518-ed5e6d559a12",
        "path" => [
          "d1653d6c-68ef-4b15-aabb-fdf3daf5064e",
          2,
          "abdc6059-1b0d-498e-ac6a-daa58cbb85fb",
        ],
      ],
    ],
  ];

  /**
   * EntityTypeManager Mock.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * CohesionLayout Mock.
   *
   * @var \Drupal\cohesion_elements\Entity\CohesionLayout|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cohesion_layout;

  /**
   * CDFObject.
   *
   * @var \Acquia\ContentHubClient\CDF\CDFObject
   */
  protected $cdf;

  /**
   * {@inheritDoc}
   */
  protected function setUp() {
    parent::setUp();

    $cohesion_layout = $this->getMockBuilder(CohesionLayout::class)
      ->disableOriginalConstructor()
      ->getMock();
    $cohesion_layout->method('getLayoutCanvasInstance')
      ->willReturn(new LayoutCanvas(self::JSON_VALUES));
    $this->cohesion_layout = $cohesion_layout;

    $entityStorage = $this->getMockBuilder(EntityStorageInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityStorage->expects($this->any())
      ->method('load')
      ->willReturnCallback(function ($argument) {
        $nodeMock = $this->getMockBuilder(NodeInterface::class)
          ->disableOriginalConstructor()
          ->getMock();
        $nodeMock->expects($this->any())
          ->method('uuid')
          ->will($this->returnValue(self::NODE_UUIDS[$argument]));

        return $nodeMock;
      });


    $entityTypeManager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->willReturn($entityStorage);
    $this->entityTypeManager = $entityTypeManager;

    $timestamp = date('c');
    $this->cdf = new CDFObject(
      self::ENTITY_TYPE,
      self::LAYOUT_CANVAS_UUID,
      $timestamp,
      $timestamp,
      self::LAYOUT_CANVAS_UUID,
      self::LAYOUT_CANVAS_METADATA
    );
  }

  /**
   * Tests links serialization on publishing of CohesionLayout field.
   */
  public function testOnSerializeCohesionLayout() {

    $subscriber = new CohesionLayoutSerializationHandler($this->entityTypeManager);
    $event = new SerializeAdditionalMetadataEvent($this->cohesion_layout, $this->cdf);
    $this->assertArrayEquals(
      self::LINKS,
      $this->cohesion_layout->getLayoutCanvasInstance()->getLinksReferences()
    );

    $subscriber->onSerializeCohesionLayout($event);
    $metadata = $event->getCdf()->getMetadata();

    $this->assertTrue(isset($metadata['additional_data']['processed_links']));
    $this->assertArrayEquals(
      self::LINKS_METADATA,
      $metadata['additional_data']['processed_links']
    );
  }

}
