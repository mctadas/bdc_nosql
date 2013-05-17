<?php

class NormalizationTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');
        
        require_once( APPLICATION_PATH . '/modules/normalize/handler/NormalizeTableJoinHandler.php');
        
        $this->bootstrap = new Zend_Application( 'staging', APPLICATION_PATH . '/configs/application.ini');
        
        parent::setUp();
        
        $this->handler = new NormalizeTableJoinHandler();
        
        $tableData = base64_decode( 'SU5TRVJUIElOVE8gYHR3aXR0ZXJfYWRhcHRlcmAgVkFMVUVTICgnYzAzODQzZTctOTI3ZC00YWVmLTg4Y2EtMzcwMTM3ZmYzZmI2JywgJzEwJywgJ3RyZW5keicsICdEb21haW5cXEFkYXB0ZXJcXFR3aXR0ZXJcXENvbW1hbmRcXFJlY2VpdmVUcmVuZHMnKTsKSU5TRVJUIElOVE8gYHR3aXR0ZXJfYWRhcHRlcl9kYXRhYCBWQUxVRVMgKCcwMGNjM2ZlOC00NWYzLTRkZGMtOGViMC0yM2E2OWY0ZWZiYzMnLCAnMjAxMi0wNy0zMSAwOTowMzoxNycsICdPOjQwOlwiRG9tYWluXFxBZGFwdGVyXFxUd2l0dGVyXFxWYWx1ZU9iamVjdFxcVHJlbmRcIjoyOntzOjg6XCJpZGVudGl0eVwiO086MjE6XCJCYjR3XFxWYWx1ZU9iamVjdFxcVXVpZFwiOjE6e3M6NTpcInZhbHVlXCI7czozNjpcIjAwY2MzZmU4LTQ1ZjMtNGRkYy04ZWIwLTIzYTY5ZjRlZmJjM1wiO31zOjEwOlwiYXR0cmlidXRlc1wiO086Mjc6XCJCYjR3XFxWYWx1ZU9iamVjdFxcQXR0cmlidXRlc1wiOjE6e3M6NTpcInZhbHVlXCI7Tzo4Olwic3RkQ2xhc3NcIjo1OntzOjQ6XCJuYW1lXCI7czoxMzpcIiN0cm9zcGlkZ2FtZXNcIjtzOjU6XCJxdWVyeVwiO3M6MTM6XCIjdHJvc3BpZGdhbWVzXCI7czoxNjpcInByb21vdGVkX2NvbnRlbnRcIjtzOjA6XCJcIjtzOjY6XCJldmVudHNcIjtzOjA6XCJcIjtzOjQ6XCJkYXRlXCI7czoxMDpcIjIwMTItMDctMjhcIjt9fX0nLCAnYzAzODQzZTctOTI3ZC00YWVmLTg4Y2EtMzcwMTM3ZmYzZmI2Jyk7CklOU0VSVCBJTlRPIGB0d2l0dGVyX2FkYXB0ZXJfZGF0YWAgVkFMVUVTICgnMDFkMTZiMjItODljZS00M2NjLThmOTEtNjkzYzU4MjQ5NmFiJywgJzIwMTItMDctMzEgMDk6MDM6MTcnLCAnTzo0MDpcIkRvbWFpblxcQWRhcHRlclxcVHdpdHRlclxcVmFsdWVPYmplY3RcXFRyZW5kXCI6Mjp7czo4OlwiaWRlbnRpdHlcIjtPOjIxOlwiQmI0d1xcVmFsdWVPYmplY3RcXFV1aWRcIjoxOntzOjU6XCJ2YWx1ZVwiO3M6MzY6XCIwMWQxNmIyMi04OWNlLTQzY2MtOGY5MS02OTNjNTgyNDk2YWJcIjt9czoxMDpcImF0dHJpYnV0ZXNcIjtPOjI3OlwiQmI0d1xcVmFsdWVPYmplY3RcXEF0dHJpYnV0ZXNcIjoxOntzOjU6XCJ2YWx1ZVwiO086ODpcInN0ZENsYXNzXCI6NTp7czo0OlwibmFtZVwiO3M6MjY6XCJIYXBweSBOYXRpb25hbCBUZXF1aWxhIERheVwiO3M6NTpcInF1ZXJ5XCI7czoyODpcIlwiSGFwcHkgTmF0aW9uYWwgVGVxdWlsYSBEYXlcIlwiO3M6MTY6XCJwcm9tb3RlZF9jb250ZW50XCI7czowOlwiXCI7czo2OlwiZXZlbnRzXCI7czowOlwiXCI7czo0OlwiZGF0ZVwiO3M6MTA6XCIyMDEyLTA3LTI2XCI7fX19JywgJ2MwMzg0M2U3LTkyN2QtNGFlZi04OGNhLTM3MDEzN2ZmM2ZiNicpOwpJTlNFUlQgSU5UTyBgdHdpdHRlcl9hZGFwdGVyX2RhdGFgIFZBTFVFUyAoJzAzMjRkZjc1LWYzZTgtNDNjMi05N2I4LTZjNTdkZmQ0OGQyZicsICcyMDEyLTA3LTMxIDA5OjAzOjE3JywgJ086NDA6XCJEb21haW5cXEFkYXB0ZXJcXFR3aXR0ZXJcXFZhbHVlT2JqZWN0XFxUcmVuZFwiOjI6e3M6ODpcImlkZW50aXR5XCI7TzoyMTpcIkJiNHdcXFZhbHVlT2JqZWN0XFxVdWlkXCI6MTp7czo1OlwidmFsdWVcIjtzOjM2OlwiMDMyNGRmNzUtZjNlOC00M2MyLTk3YjgtNmM1N2RmZDQ4ZDJmXCI7fXM6MTA6XCJhdHRyaWJ1dGVzXCI7TzoyNzpcIkJiNHdcXFZhbHVlT2JqZWN0XFxBdHRyaWJ1dGVzXCI6MTp7czo1OlwidmFsdWVcIjtPOjg6XCJzdGRDbGFzc1wiOjU6e3M6NDpcIm5hbWVcIjtzOjExOlwiRGFubnkgQm95bGVcIjtzOjU6XCJxdWVyeVwiO3M6MTM6XCJcIkRhbm55IEJveWxlXCJcIjtzOjE2OlwicHJvbW90ZWRfY29udGVudFwiO3M6MDpcIlwiO3M6NjpcImV2ZW50c1wiO3M6MDpcIlwiO3M6NDpcImRhdGVcIjtzOjEwOlwiMjAxMi0wNy0yOFwiO319fScsICdjMDM4NDNlNy05MjdkLTRhZWYtODhjYS0zNzAxMzdmZjNmYjYnKTsKSU5TRVJUIElOVE8gYHR3aXR0ZXJfYWRhcHRlcl9kYXRhYCBWQUxVRVMgKCcwNDA4NTA4MC02OGY5LTQ4MjAtOGM3Ni0zZTM3MGU0MWQ1YWUnLCAnMjAxMi0wNy0zMSAwOTowMzoxNycsICdPOjQwOlwiRG9tYWluXFxBZGFwdGVyXFxUd2l0dGVyXFxWYWx1ZU9iamVjdFxcVHJlbmRcIjoyOntzOjg6XCJpZGVudGl0eVwiO086MjE6XCJCYjR3XFxWYWx1ZU9iamVjdFxcVXVpZFwiOjE6e3M6NTpcInZhbHVlXCI7czozNjpcIjA0MDg1MDgwLTY4ZjktNDgyMC04Yzc2LTNlMzcwZTQxZDVhZVwiO31zOjEwOlwiYXR0cmlidXRlc1wiO086Mjc6XCJCYjR3XFxWYWx1ZU9iamVjdFxcQXR0cmlidXRlc1wiOjE6e3M6NTpcInZhbHVlXCI7Tzo4Olwic3RkQ2xhc3NcIjo1OntzOjQ6XCJuYW1lXCI7czoyODpcIiNXaGl0ZVBwbERvSXRCdXRCbGFja1BwbERvbnRcIjtzOjU6XCJxdWVyeVwiO3M6Mjg6XCIjV2hpdGVQcGxEb0l0QnV0QmxhY2tQcGxEb250XCI7czoxNjpcInByb21vdGVkX2NvbnRlbnRcIjtzOjA6XCJcIjtzOjY6XCJldmVudHNcIjtzOjA6XCJcIjtzOjQ6XCJkYXRlXCI7czoxMDpcIjIwMTItMDctMjVcIjt9fX0nLCAnYzAzODQzZTctOTI3ZC00YWVmLTg4Y2EtMzcwMTM3ZmYzZmI2Jyk7CklOU0VSVCBJTlRPIGB0d2l0dGVyX2FkYXB0ZXJfZGF0YWAgVkFMVUVTICgnMDRkZjczMTQtNzI4Yy00YTI5LTk1NDctZmU5ZDc4NmY3YWU3JywgJzIwMTItMDctMzEgMDk6MDM6MTcnLCAnTzo0MDpcIkRvbWFpblxcQWRhcHRlclxcVHdpdHRlclxcVmFsdWVPYmplY3RcXFRyZW5kXCI6Mjp7czo4OlwiaWRlbnRpdHlcIjtPOjIxOlwiQmI0d1xcVmFsdWVPYmplY3RcXFV1aWRcIjoxOntzOjU6XCJ2YWx1ZVwiO3M6MzY6XCIwNGRmNzMxNC03MjhjLTRhMjktOTU0Ny1mZTlkNzg2ZjdhZTdcIjt9czoxMDpcImF0dHJpYnV0ZXNcIjtPOjI3OlwiQmI0d1xcVmFsdWVPYmplY3RcXEF0dHJpYnV0ZXNcIjoxOntzOjU6XCJ2YWx1ZVwiO086ODpcInN0ZENsYXNzXCI6NTp7czo0OlwibmFtZVwiO3M6OTpcIlRvbSBEYWxleVwiO3M6NTpcInF1ZXJ5XCI7czoxMTpcIlwiVG9tIERhbGV5XCJcIjtzOjE2OlwicHJvbW90ZWRfY29udGVudFwiO3M6MDpcIlwiO3M6NjpcImV2ZW50c1wiO3M6MDpcIlwiO3M6NDpcImRhdGVcIjtzOjEwOlwiMjAxMi0wNy0zMVwiO319fScsICdjMDM4NDNlNy05MjdkLTRhZWYtODhjYS0zNzAxMzdmZjNmYjYnKTsKSU5TRVJUIElOVE8gYHR3aXR0ZXJfYWRhcHRlcl9kYXRhYCBWQUxVRVMgKCcwNjNhNWI3Ni0xMDE2LTRhZDEtYmRiYS0zNzE0ZWI4MDA5Y2YnLCAnMjAxMi0wNy0zMSAwOTowMzoxNycsICdPOjQwOlwiRG9tYWluXFxBZGFwdGVyXFxUd2l0dGVyXFxWYWx1ZU9iamVjdFxcVHJlbmRcIjoyOntzOjg6XCJpZGVudGl0eVwiO086MjE6XCJCYjR3XFxWYWx1ZU9iamVjdFxcVXVpZFwiOjE6e3M6NTpcInZhbHVlXCI7czozNjpcIjA2M2E1Yjc2LTEwMTYtNGFkMS1iZGJhLTM3MTRlYjgwMDljZlwiO31zOjEwOlwiYXR0cmlidXRlc1wiO086Mjc6XCJCYjR3XFxWYWx1ZU9iamVjdFxcQXR0cmlidXRlc1wiOjE6e3M6NTpcInZhbHVlXCI7Tzo4Olwic3RkQ2xhc3NcIjo1OntzOjQ6XCJuYW1lXCI7czoxNTpcIiNlbmJhYmF5YWxhbmxhclwiO3M6NTpcInF1ZXJ5XCI7czoxNTpcIiNlbmJhYmF5YWxhbmxhclwiO3M6MTY6XCJwcm9tb3RlZF9jb250ZW50XCI7czowOlwiXCI7czo2OlwiZXZlbnRzXCI7czowOlwiXCI7czo0OlwiZGF0ZVwiO3M6MTA6XCIyMDEyLTA3LTI3XCI7fX19JywgJ2MwMzg0M2U3LTkyN2QtNGFlZi04OGNhLTM3MDEzN2ZmM2ZiNicpOwpJTlNFUlQgSU5UTyBgdHdpdHRlcl9hZGFwdGVyX2RhdGFgIFZBTFVFUyAoJzA3YTE1YzkzLWQzNzItNGI5Yi05OWU5LTQ1OTYwMjRlYjYwNicsICcyMDEyLTA3LTMxIDA5OjAzOjE3JywgJ086NDA6XCJEb21haW5cXEFkYXB0ZXJcXFR3aXR0ZXJcXFZhbHVlT2JqZWN0XFxUcmVuZFwiOjI6e3M6ODpcImlkZW50aXR5XCI7TzoyMTpcIkJiNHdcXFZhbHVlT2JqZWN0XFxVdWlkXCI6MTp7czo1OlwidmFsdWVcIjtzOjM2OlwiMDdhMTVjOTMtZDM3Mi00YjliLTk5ZTktNDU5NjAyNGViNjA2XCI7fXM6MTA6XCJhdHRyaWJ1dGVzXCI7TzoyNzpcIkJiNHdcXFZhbHVlT2JqZWN0XFxBdHRyaWJ1dGVzXCI6MTp7czo1OlwidmFsdWVcIjtPOjg6XCJzdGRDbGFzc1wiOjU6e3M6NDpcIm5hbWVcIjtzOjI3OlwiI1N0dWZmWW91U2F5V2hlbllvdUxvc2VJbjJLXCI7czo1OlwicXVlcnlcIjtzOjI3OlwiI1N0dWZmWW91U2F5V2hlbllvdUxvc2VJbjJLXCI7czoxNjpcInByb21vdGVkX2NvbnRlbnRcIjtzOjA6XCJcIjtzOjY6XCJldmVudHNcIjtzOjA6XCJcIjtzOjQ6XCJkYXRlXCI7czoxMDpcIjIwMTItMDctMjVcIjt9fX0nLCAnYzAzODQzZTctOTI3ZC00YWVmLTg4Y2EtMzcwMTM3ZmYzZmI2Jyk7CklOU0VSVCBJTlRPIGB0d2l0dGVyX2FkYXB0ZXJfZGF0YWAgVkFMVUVTICgnMDhjODgzM2MtMjgzZS00MTFjLWIzYWQtNzFmN2IyMTUxNTA0JywgJzIwMTItMDctMzEgMDk6MDM6MTcnLCAnTzo0MDpcIkRvbWFpblxcQWRhcHRlclxcVHdpdHRlclxcVmFsdWVPYmplY3RcXFRyZW5kXCI6Mjp7czo4OlwiaWRlbnRpdHlcIjtPOjIxOlwiQmI0d1xcVmFsdWVPYmplY3RcXFV1aWRcIjoxOntzOjU6XCJ2YWx1ZVwiO3M6MzY6XCIwOGM4ODMzYy0yODNlLTQxMWMtYjNhZC03MWY3YjIxNTE1MDRcIjt9czoxMDpcImF0dHJpYnV0ZXNcIjtPOjI3OlwiQmI0d1xcVmFsdWVPYmplY3RcXEF0dHJpYnV0ZXNcIjoxOntzOjU6XCJ2YWx1ZVwiO086ODpcInN0ZENsYXNzXCI6NTp7czo0OlwibmFtZVwiO3M6MjQ6XCJXZSBOZWVkIEpvbmFzIFdvcmxkIFRvdXJcIjtzOjU6XCJxdWVyeVwiO3M6MjY6XCJcIldlIE5lZWQgSm9uYXMgV29ybGQgVG91clwiXCI7czoxNjpcInByb21vdGVkX2NvbnRlbnRcIjtzOjA6XCJcIjtzOjY6XCJldmVudHNcIjtzOjA6XCJcIjtzOjQ6XCJkYXRlXCI7czoxMDpcIjIwMTItMDctMjdcIjt9fX0nLCAnYzAzODQzZTctOTI3ZC00YWVmLTg4Y2EtMzcwMTM3ZmYzZmI2Jyk7CklOU0VSVCBJTlRPIGB0d2l0dGVyX2FkYXB0ZXJfZGF0YWAgVkFMVUVTICgnMDhlMjdjZWQtM2VlYy00ZDlkLWE2NGMtY2Y1ZWRmZTYxMWNjJywgJzIwMTItMDctMzEgMDk6MDM6MTcnLCAnTzo0MDpcIkRvbWFpblxcQWRhcHRlclxcVHdpdHRlclxcVmFsdWVPYmplY3RcXFRyZW5kXCI6Mjp7czo4OlwiaWRlbnRpdHlcIjtPOjIxOlwiQmI0d1xcVmFsdWVPYmplY3RcXFV1aWRcIjoxOntzOjU6XCJ2YWx1ZVwiO3M6MzY6XCIwOGUyN2NlZC0zZWVjLTRkOWQtYTY0Yy1jZjVlZGZlNjExY2NcIjt9czoxMDpcImF0dHJpYnV0ZXNcIjtPOjI3OlwiQmI0d1xcVmFsdWVPYmplY3RcXEF0dHJpYnV0ZXNcIjoxOntzOjU6XCJ2YWx1ZVwiO086ODpcInN0ZENsYXNzXCI6NTp7czo0OlwibmFtZVwiO3M6MTc6XCJBbmRyaXkgU2hldmNoZW5rb1wiO3M6NTpcInF1ZXJ5XCI7czoxOTpcIlwiQW5kcml5IFNoZXZjaGVua29cIlwiO3M6MTY6XCJwcm9tb3RlZF9jb250ZW50XCI7czowOlwiXCI7czo2OlwiZXZlbnRzXCI7czowOlwiXCI7czo0OlwiZGF0ZVwiO3M6MTA6XCIyMDEyLTA3LTI5XCI7fX19JywgJ2MwMzg0M2U3LTkyN2QtNGFlZi04OGNhLTM3MDEzN2ZmM2ZiNicpOwpJTlNFUlQgSU5UTyBgdHdpdHRlcl9hZGFwdGVyX2RhdGFgIFZBTFVFUyAoJzA5NDMzMDc4LTQ5NGUtNDkyOC1hMzhlLTZmZWJiNDQwNDhmYScsICcyMDEyLTA3LTMxIDA5OjAzOjE3JywgJ086NDA6XCJEb21haW5cXEFkYXB0ZXJcXFR3aXR0ZXJcXFZhbHVlT2JqZWN0XFxUcmVuZFwiOjI6e3M6ODpcImlkZW50aXR5XCI7TzoyMTpcIkJiNHdcXFZhbHVlT2JqZWN0XFxVdWlkXCI6MTp7czo1OlwidmFsdWVcIjtzOjM2OlwiMDk0MzMwNzgtNDk0ZS00OTI4LWEzOGUtNmZlYmI0NDA0OGZhXCI7fXM6MTA6XCJhdHRyaWJ1dGVzXCI7TzoyNzpcIkJiNHdcXFZhbHVlT2JqZWN0XFxBdHRyaWJ1dGVzXCI6MTp7czo1OlwidmFsdWVcIjtPOjg6XCJzdGRDbGFzc1wiOjU6e3M6NDpcIm5hbWVcIjtzOjMyOlwiSGFycnkgUG90dGVyIElzIFRoZSBCZXN0IEZvcmV2ZXJcIjtzOjU6XCJxdWVyeVwiO3M6MzQ6XCJcIkhhcnJ5IFBvdHRlciBJcyBUaGUgQmVzdCBGb3JldmVyXCJcIjtzOjE2OlwicHJvbW90ZWRfY29udGVudFwiO3M6MDpcIlwiO3M6NjpcImV2ZW50c1wiO3M6MDpcIlwiO3M6NDpcImRhdGVcIjtzOjEwOlwiMjAxMi0wNy0yNlwiO319fScsICdjMDM4NDNlNy05MjdkLTRhZWYtODhjYS0zNzAxMzdmZjNmYjYnKTsKSU5TRVJUIElOVE8gYHR3aXR0ZXJfYWRhcHRlcl9kYXRhYCBWQUxVRVMgKCcwYmUwYWRlMS03ODE2LTQ0MzctYTg2Ni02MGVhZTAxNzY3OTQnLCAnMjAxMi0wNy0zMSAwOTowMzoxNycsICdPOjQwOlwiRG9tYWluXFxBZGFwdGVyXFxUd2l0dGVyXFxWYWx1ZU9iamVjdFxcVHJlbmRcIjoyOntzOjg6XCJpZGVudGl0eVwiO086MjE6XCJCYjR3XFxWYWx1ZU9iamVjdFxcVXVpZFwiOjE6e3M6NTpcInZhbHVlXCI7czozNjpcIjBiZTBhZGUxLTc4MTYtNDQzNy1hODY2LTYwZWFlMDE3Njc5NFwiO31zOjEwOlwiYXR0cmlidXRlc1wiO086Mjc6XCJCYjR3XFxWYWx1ZU9iamVjdFxcQXR0cmlidXRlc1wiOjE6e3M6NTpcInZhbHVlXCI7Tzo4Olwic3RkQ2xhc3NcIjo1OntzOjQ6XCJuYW1lXCI7czoxMzpcIk1hY2hldGUgS2lsbHNcIjtzOjU6XCJxdWVyeVwiO3M6MTU6XCJcIk1hY2hldGUgS2lsbHNcIlwiO3M6MTY6XCJwcm9tb3RlZF9jb250ZW50XCI7czowOlwiXCI7czo2OlwiZXZlbnRzXCI7czowOlwiXCI7czo0OlwiZGF0ZVwiO3M6MTA6XCIyMDEyLTA3LTI4XCI7fX19JywgJ2MwMzg0M2U3LTkyN2QtNGFlZi04OGNhLTM3MDEzN2ZmM2ZiNicpOwpJTlNFUlQgSU5UTyBgdHdpdHRlcl9hZGFwdGVyX2RhdGFgIFZBTFVFUyAoJzBiZTI3OWM3LWQ4ZjEtNDVmMy05OTQ0LTkwNGUxYzY0ZTIzNCcsICcyMDEyLTA3LTMxIDA5OjAzOjE3JywgJ086NDA6XCJEb21haW5cXEFkYXB0ZXJcXFR3aXR0ZXJcXFZhbHVlT2JqZWN0XFxUcmVuZFwiOjI6e3M6ODpcImlkZW50aXR5XCI7TzoyMTpcIkJiNHdcXFZhbHVlT2JqZWN0XFxVdWlkXCI6MTp7czo1OlwidmFsdWVcIjtzOjM2OlwiMGJlMjc5YzctZDhmMS00NWYzLTk5NDQtOTA0ZTFjNjRlMjM0XCI7fXM6MTA6XCJhdHRyaWJ1dGVzXCI7TzoyNzpcIkJiNHdcXFZhbHVlT2JqZWN0XFxBdHRyaWJ1dGVzXCI6MTp7czo1OlwidmFsdWVcIjtPOjg6XCJzdGRDbGFzc1wiOjU6e3M6NDpcIm5hbWVcIjtzOjIwOlwiIzEwQ29pc2FzUXVlQW1vRmF6ZXJcIjtzOjU6XCJxdWVyeVwiO3M6MjA6XCIjMTBDb2lzYXNRdWVBbW9GYXplclwiO3M6MTY6XCJwcm9tb3RlZF9jb250ZW50XCI7czowOlwiXCI7czo2OlwiZXZlbnRzXCI7czowOlwiXCI7czo0OlwiZGF0ZVwiO3M6MTA6XCIyMDEyLTA3LTMxXCI7fX19JywgJ2MwMzg0M2U3LTkyN2QtNGFlZi04OGNhLTM3MDEzN2ZmM2ZiNicpOwpJTlNFUlQgSU5UTyBgdHdpdHRlcl9hZGFwdGVyX21ldGFgIFZBTFVFUyAoJ2MwMzg0M2U3LTkyN2QtNGFlZi04OGNhLTM3MDEzN2ZmM2ZiNicsICduYW1lJywgJ3ZhcmNoYXInKTsKSU5TRVJUIElOVE8gYHR3aXR0ZXJfYWRhcHRlcl9tZXRhYCBWQUxVRVMgKCdjMDM4NDNlNy05MjdkLTRhZWYtODhjYS0zNzAxMzdmZjNmYjYnLCAncXVlcnknLCAndmFyY2hhcicpOwpJTlNFUlQgSU5UTyBgdHdpdHRlcl9hZGFwdGVyX21ldGFgIFZBTFVFUyAoJ2MwMzg0M2U3LTkyN2QtNGFlZi04OGNhLTM3MDEzN2ZmM2ZiNicsICdwcm9tb3RlZF9jb250ZW50JywgJ3ZhcmNoYXInKTsKSU5TRVJUIElOVE8gYHR3aXR0ZXJfYWRhcHRlcl9tZXRhYCBWQUxVRVMgKCdjMDM4NDNlNy05MjdkLTRhZWYtODhjYS0zNzAxMzdmZjNmYjYnLCAnZXZlbnRzJywgJ3ZhcmNoYXInKTsKSU5TRVJUIElOVE8gYHR3aXR0ZXJfYWRhcHRlcl9tZXRhYCBWQUxVRVMgKCdjMDM4NDNlNy05MjdkLTRhZWYtODhjYS0zNzAxMzdmZjNmYjYnLCAnZGF0ZScsICdkYXRlJyk7CklOU0VSVCBJTlRPIGBmYWNlYm9va19hZGFwdGVyYCBWQUxVRVMgKCdlMmI3ZGQ5Yi0xZjBhLTQ1YjUtYTdkYy04YmM4YzhkMzlmM2YnLCAnMTAnLCAnRmFjZWJvb2sgcGFpZcWha2EgJywgJ0RvbWFpblxcQWRhcHRlclxcRmFjZWJvb2tcXENvbW1hbmRcXEZpbmRNZW50aW9uJyk7CklOU0VSVCBJTlRPIGBmYWNlYm9va19hZGFwdGVyX2RhdGFgIFZBTFVFUyAoJzRjOThjZGMxLTUzNDAtNGZjMy1iM2NiLTE2NWM2NDc3ZDk5YicsICcyMDEyLTA3LTE2IDE1OjI4OjA0JywgJ086NDM6XCJEb21haW5cXEFkYXB0ZXJcXEZhY2Vib29rXFxWYWx1ZU9iamVjdFxcTWVudGlvblwiOjI6e3M6ODpcImlkZW50aXR5XCI7TzoyMTpcIkJiNHdcXFZhbHVlT2JqZWN0XFxVdWlkXCI6MTp7czo1OlwidmFsdWVcIjtzOjM2OlwiNGM5OGNkYzEtNTM0MC00ZmMzLWIzY2ItMTY1YzY0NzdkOTliXCI7fXM6MTA6XCJhdHRyaWJ1dGVzXCI7TzoyNzpcIkJiNHdcXFZhbHVlT2JqZWN0XFxBdHRyaWJ1dGVzXCI6MTp7czo1OlwidmFsdWVcIjtPOjg6XCJzdGRDbGFzc1wiOjE2OntzOjI6XCJpZFwiO3M6MzE6XCIxMDAwMDEwNjMyOTM4NjdfNDQwNDc0OTEyNjQyMTYwXCI7czo5OlwiZnJvbV9uYW1lXCI7czoxOTpcIkFpc3RlIFBhdWxpdWtvbmllbmVcIjtzOjc6XCJmcm9tX2lkXCI7czoxNTpcIjEwMDAwMTA2MzI5Mzg2N1wiO3M6NTpcInN0b3J5XCI7czo1NTpcIkFpc3RlIFBhdWxpdWtvbmllbmUgc2hhcmVkIERyecW+dW90YSBwYWtyYW50xJdcJ3MgcGhvdG8uXCI7czo3OlwicGljdHVyZVwiO3M6ODM6XCJodHRwOi8vcGhvdG9zLWguYWsuZmJjZG4ubmV0L2hwaG90b3MtYWstc25jNi82MDM1NTRfNDA2MDIxMjE2MTI4ODc0XzIzNTg1NDA1Ml9zLmpwZ1wiO3M6NDpcImxpbmtcIjtzOjEwNTpcImh0dHA6Ly93d3cuZmFjZWJvb2suY29tL3Bob3RvLnBocD9mYmlkPTQwNjAyMTIxNjEyODg3NCZzZXQ9YS4zNjY5OTY0OTAwMzEzNDcuODY2ODIuMzY2Mzk0NTI2NzU4MjEwJnR5cGU9MVwiO3M6NDpcIm5hbWVcIjtzOjExOlwiV2FsbCBQaG90b3NcIjtzOjc6XCJjYXB0aW9uXCI7czozNzk6XCJKYXUgcnl0b2ogbcWrc8WzIHBhZ2FsdsSXbMSXIFwiT3JhbsW+aW5pcyDFoXZ5dHVyeXNcIiBpxaFrZWxpYXVzIMSvIG5hdWp1cyBuYW11xI1pdXMuIE5la2FudHJhdWphbWUhIE8gamVpIGRhciBuZXNwxJdqYWkgc3VkYWx5dmF1dGkgbcWrc8WzIGtvbmt1cnNlLCBkYWJhciBkYXIgZ2FsaSB0YWkgcGFkYXJ5dGkuLi4gOy0pXHJcblxyXG5Lb25rdXJzYXMgxI1pYTogaHR0cDovL3d3dy5mYWNlYm9vay5jb20vbWVkaWEvc2V0Lz9zZXQ9YS4zODIyMjU3NTUxNzUwODcuODc5NjIuMzY2Mzk0NTI2NzU4MjEwJnR5cGU9MyMhL3Bob3RvLnBocD9mYmlkPTQwMTE2NzE0OTk0NzYxNCZzZXQ9YS4zNjY5OTY0OTAwMzEzNDcuODY2ODIuMzY2Mzk0NTI2NzU4MjEwJnR5cGU9MSZ0aGVhdGVyXCI7czoxMDpcInByb3BlcnRpZXNcIjtzOjE2NjpcImE6MTp7aTowO2E6Mzp7czo0OlwibmFtZVwiO3M6MjpcIkJ5XCI7czo0OlwidGV4dFwiO3M6MTk6XCJEcnnFvnVvdGEgcGFrcmFudMSXXCI7czo0OlwiaHJlZlwiO3M6NzM6XCJodHRwOi8vd3d3LmZhY2Vib29rLmNvbS9wYWdlcy9EcnklQzUlQkV1b3RhLXBha3JhbnQlQzQlOTcvMzY2Mzk0NTI2NzU4MjEwXCI7fX1cIjtzOjQ6XCJpY29uXCI7czo1OTpcImh0dHA6Ly9zdGF0aWMuYWsuZmJjZG4ubmV0L3JzcmMucGhwL3YyL3lEL3IvYVM4ZWNtWVJ5czAuZ2lmXCI7czo0OlwidHlwZVwiO3M6NTpcInBob3RvXCI7czo5Olwib2JqZWN0X2lkXCI7czoxNTpcIjQwNjAyMTIxNjEyODg3NFwiO3M6MTY6XCJhcHBsaWNhdGlvbl9uYW1lXCI7czo2OlwiUGhvdG9zXCI7czoxNDpcImFwcGxpY2F0aW9uX2lkXCI7czoxMDpcIjIzMDUyNzI3MzJcIjtzOjEyOlwiY3JlYXRlZF90aW1lXCI7czoyNDpcIjIwMTItMDctMTRUMTk6MDE6MDYrMDAwMFwiO3M6MTI6XCJ1cGRhdGVkX3RpbWVcIjtzOjI0OlwiMjAxMi0wNy0xNFQxOTowMTowNiswMDAwXCI7fX19JywgJ2UyYjdkZDliLTFmMGEtNDViNS1hN2RjLThiYzhjOGQzOWYzZicpOwpJTlNFUlQgSU5UTyBgZmFjZWJvb2tfYWRhcHRlcl9kYXRhYCBWQUxVRVMgKCc1MDJmYjhhYy0xZWY1LTQ4YzQtYjc4Yy1jZmM0NDc0MDE1YjQnLCAnMjAxMi0wNy0xNiAxNToyODowNCcsICdPOjQzOlwiRG9tYWluXFxBZGFwdGVyXFxGYWNlYm9va1xcVmFsdWVPYmplY3RcXE1lbnRpb25cIjoyOntzOjg6XCJpZGVudGl0eVwiO086MjE6XCJCYjR3XFxWYWx1ZU9iamVjdFxcVXVpZFwiOjE6e3M6NTpcInZhbHVlXCI7czozNjpcIjUwMmZiOGFjLTFlZjUtNDhjNC1iNzhjLWNmYzQ0NzQwMTViNFwiO31zOjEwOlwiYXR0cmlidXRlc1wiO086Mjc6XCJCYjR3XFxWYWx1ZU9iamVjdFxcQXR0cmlidXRlc1wiOjE6e3M6NTpcInZhbHVlXCI7Tzo4Olwic3RkQ2xhc3NcIjoxNjp7czoyOlwiaWRcIjtzOjMxOlwiMTAwMDAxMDYzMjkzODY3XzQzNzc5MTM3NjI0MzcwMFwiO3M6OTpcImZyb21fbmFtZVwiO3M6MTk6XCJBaXN0ZSBQYXVsaXVrb25pZW5lXCI7czo3OlwiZnJvbV9pZFwiO3M6MTU6XCIxMDAwMDEwNjMyOTM4NjdcIjtzOjU6XCJzdG9yeVwiO3M6NTU6XCJBaXN0ZSBQYXVsaXVrb25pZW5lIHNoYXJlZCBEcnnFvnVvdGEgcGFrcmFudMSXXCdzIHBob3RvLlwiO3M6NzpcInBpY3R1cmVcIjtzOjgzOlwiaHR0cDovL3Bob3Rvcy1oLmFrLmZiY2RuLm5ldC9ocGhvdG9zLWFrLWFzaDMvNTk5OTA1XzQwMTE2NzE0OTk0NzYxNF82NDQ3MzE3MDdfcy5qcGdcIjtzOjQ6XCJsaW5rXCI7czoxMDU6XCJodHRwOi8vd3d3LmZhY2Vib29rLmNvbS9waG90by5waHA/ZmJpZD00MDExNjcxNDk5NDc2MTQmc2V0PWEuMzY2OTk2NDkwMDMxMzQ3Ljg2NjgyLjM2NjM5NDUyNjc1ODIxMCZ0eXBlPTFcIjtzOjQ6XCJuYW1lXCI7czoxMTpcIldhbGwgUGhvdG9zXCI7czo3OlwiY2FwdGlvblwiO3M6MTM1OlwiU3BhdXNrIFwicGF0aW5rYVwiIGJlaSBcImRhbGludGlzXCIgaXIgdmllbmFtIErFq3PFsywgcGVyIHBhdMSvIHZpZHVydmFzYXLEryAoMDcuMTVkLiksIHBhZG92YW5vc2ltZSBwYWdhbHbEl2zEmSBcIk9yYW7FvmluaXMgxaF2eXR1cnlzXCIhIDopXCI7czoxMDpcInByb3BlcnRpZXNcIjtzOjE2NjpcImE6MTp7aTowO2E6Mzp7czo0OlwibmFtZVwiO3M6MjpcIkJ5XCI7czo0OlwidGV4dFwiO3M6MTk6XCJEcnnFvnVvdGEgcGFrcmFudMSXXCI7czo0OlwiaHJlZlwiO3M6NzM6XCJodHRwOi8vd3d3LmZhY2Vib29rLmNvbS9wYWdlcy9EcnklQzUlQkV1b3RhLXBha3JhbnQlQzQlOTcvMzY2Mzk0NTI2NzU4MjEwXCI7fX1cIjtzOjQ6XCJpY29uXCI7czo1OTpcImh0dHA6Ly9zdGF0aWMuYWsuZmJjZG4ubmV0L3JzcmMucGhwL3YyL3lEL3IvYVM4ZWNtWVJ5czAuZ2lmXCI7czo0OlwidHlwZVwiO3M6NTpcInBob3RvXCI7czo5Olwib2JqZWN0X2lkXCI7czoxNTpcIjQwMTE2NzE0OTk0NzYxNFwiO3M6MTY6XCJhcHBsaWNhdGlvbl9uYW1lXCI7czo2OlwiUGhvdG9zXCI7czoxNDpcImFwcGxpY2F0aW9uX2lkXCI7czoxMDpcIjIzMDUyNzI3MzJcIjtzOjEyOlwiY3JlYXRlZF90aW1lXCI7czoyNDpcIjIwMTItMDctMDlUMDg6MjQ6MTkrMDAwMFwiO3M6MTI6XCJ1cGRhdGVkX3RpbWVcIjtzOjI0OlwiMjAxMi0wNy0wOVQwODoyNDoxOSswMDAwXCI7fX19JywgJ2UyYjdkZDliLTFmMGEtNDViNS1hN2RjLThiYzhjOGQzOWYzZicpOwpJTlNFUlQgSU5UTyBgZmFjZWJvb2tfYWRhcHRlcl9kYXRhYCBWQUxVRVMgKCc3NzZlZGY0Zi01YjZjLTQ3ZTctYTliMi02NjgwYTA0NzFjMTMnLCAnMjAxMi0wNy0xNiAxNToyODowNCcsICdPOjQzOlwiRG9tYWluXFxBZGFwdGVyXFxGYWNlYm9va1xcVmFsdWVPYmplY3RcXE1lbnRpb25cIjoyOntzOjg6XCJpZGVudGl0eVwiO086MjE6XCJCYjR3XFxWYWx1ZU9iamVjdFxcVXVpZFwiOjE6e3M6NTpcInZhbHVlXCI7czozNjpcIjc3NmVkZjRmLTViNmMtNDdlNy1hOWIyLTY2ODBhMDQ3MWMxM1wiO31zOjEwOlwiYXR0cmlidXRlc1wiO086Mjc6XCJCYjR3XFxWYWx1ZU9iamVjdFxcQXR0cmlidXRlc1wiOjE6e3M6NTpcInZhbHVlXCI7Tzo4Olwic3RkQ2xhc3NcIjoxNjp7czoyOlwiaWRcIjtzOjMxOlwiMTAwMDAxMDYzMjkzODY3XzM4OTgzNDA5Nzc0MjczNFwiO3M6OTpcImZyb21fbmFtZVwiO3M6MTk6XCJBaXN0ZSBQYXVsaXVrb25pZW5lXCI7czo3OlwiZnJvbV9pZFwiO3M6MTU6XCIxMDAwMDEwNjMyOTM4NjdcIjtzOjU6XCJzdG9yeVwiO3M6NTU6XCJBaXN0ZSBQYXVsaXVrb25pZW5lIHNoYXJlZCBEcnnFvnVvdGEgcGFrcmFudMSXXCdzIHBob3RvLlwiO3M6NzpcInBpY3R1cmVcIjtzOjgzOlwiaHR0cDovL3Bob3Rvcy1oLmFrLmZiY2RuLm5ldC9ocGhvdG9zLWFrLWFzaDMvNTk5OTA1XzQwMTE2NzE0OTk0NzYxNF82NDQ3MzE3MDdfcy5qcGdcIjtzOjQ6XCJsaW5rXCI7czoxMDU6XCJodHRwOi8vd3d3LmZhY2Vib29rLmNvbS9waG90by5waHA/ZmJpZD00MDExNjcxNDk5NDc2MTQmc2V0PWEuMzY2OTk2NDkwMDMxMzQ3Ljg2NjgyLjM2NjM5NDUyNjc1ODIxMCZ0eXBlPTFcIjtzOjQ6XCJuYW1lXCI7czoxMTpcIldhbGwgUGhvdG9zXCI7czo3OlwiY2FwdGlvblwiO3M6MTM1OlwiU3BhdXNrIFwicGF0aW5rYVwiIGJlaSBcImRhbGludGlzXCIgaXIgdmllbmFtIErFq3PFsywgcGVyIHBhdMSvIHZpZHVydmFzYXLEryAoMDcuMTVkLiksIHBhZG92YW5vc2ltZSBwYWdhbHbEl2zEmSBcIk9yYW7FvmluaXMgxaF2eXR1cnlzXCIhIDopXCI7czoxMDpcInByb3BlcnRpZXNcIjtzOjE2NjpcImE6MTp7aTowO2E6Mzp7czo0OlwibmFtZVwiO3M6MjpcIkJ5XCI7czo0OlwidGV4dFwiO3M6MTk6XCJEcnnFvnVvdGEgcGFrcmFudMSXXCI7czo0OlwiaHJlZlwiO3M6NzM6XCJodHRwOi8vd3d3LmZhY2Vib29rLmNvbS9wYWdlcy9EcnklQzUlQkV1b3RhLXBha3JhbnQlQzQlOTcvMzY2Mzk0NTI2NzU4MjEwXCI7fX1cIjtzOjQ6XCJpY29uXCI7czo1OTpcImh0dHA6Ly9zdGF0aWMuYWsuZmJjZG4ubmV0L3JzcmMucGhwL3YyL3lEL3IvYVM4ZWNtWVJ5czAuZ2lmXCI7czo0OlwidHlwZVwiO3M6NTpcInBob3RvXCI7czo5Olwib2JqZWN0X2lkXCI7czoxNTpcIjQwMTE2NzE0OTk0NzYxNFwiO3M6MTY6XCJhcHBsaWNhdGlvbl9uYW1lXCI7czo2OlwiUGhvdG9zXCI7czoxNDpcImFwcGxpY2F0aW9uX2lkXCI7czoxMDpcIjIzMDUyNzI3MzJcIjtzOjEyOlwiY3JlYXRlZF90aW1lXCI7czoyNDpcIjIwMTItMDctMTNUMTk6MDU6MjArMDAwMFwiO3M6MTI6XCJ1cGRhdGVkX3RpbWVcIjtzOjI0OlwiMjAxMi0wNy0xM1QxOTowNToyMCswMDAwXCI7fX19JywgJ2UyYjdkZDliLTFmMGEtNDViNS1hN2RjLThiYzhjOGQzOWYzZicpOwpJTlNFUlQgSU5UTyBgZmFjZWJvb2tfYWRhcHRlcl9tZXRhYCBWQUxVRVMgKCdlMmI3ZGQ5Yi0xZjBhLTQ1YjUtYTdkYy04YmM4YzhkMzlmM2YnLCAnaWQnLCAndmFyY2hhcicpOwpJTlNFUlQgSU5UTyBgZmFjZWJvb2tfYWRhcHRlcl9tZXRhYCBWQUxVRVMgKCdlMmI3ZGQ5Yi0xZjBhLTQ1YjUtYTdkYy04YmM4YzhkMzlmM2YnLCAnZnJvbV9uYW1lJywgJ3ZhcmNoYXInKTsKSU5TRVJUIElOVE8gYGZhY2Vib29rX2FkYXB0ZXJfbWV0YWAgVkFMVUVTICgnZTJiN2RkOWItMWYwYS00NWI1LWE3ZGMtOGJjOGM4ZDM5ZjNmJywgJ2Zyb21faWQnLCAnaW50Jyk7CklOU0VSVCBJTlRPIGBmYWNlYm9va19hZGFwdGVyX21ldGFgIFZBTFVFUyAoJ2UyYjdkZDliLTFmMGEtNDViNS1hN2RjLThiYzhjOGQzOWYzZicsICdzdG9yeScsICd2YXJjaGFyJyk7CklOU0VSVCBJTlRPIGBmYWNlYm9va19hZGFwdGVyX21ldGFgIFZBTFVFUyAoJ2UyYjdkZDliLTFmMGEtNDViNS1hN2RjLThiYzhjOGQzOWYzZicsICdwaWN0dXJlJywgJ3ZhcmNoYXInKTsKSU5TRVJUIElOVE8gYGZhY2Vib29rX2FkYXB0ZXJfbWV0YWAgVkFMVUVTICgnZTJiN2RkOWItMWYwYS00NWI1LWE3ZGMtOGJjOGM4ZDM5ZjNmJywgJ2xpbmsnLCAndmFyY2hhcicpOwpJTlNFUlQgSU5UTyBgZmFjZWJvb2tfYWRhcHRlcl9tZXRhYCBWQUxVRVMgKCdlMmI3ZGQ5Yi0xZjBhLTQ1YjUtYTdkYy04YmM4YzhkMzlmM2YnLCAnbmFtZScsICd2YXJjaGFyJyk7CklOU0VSVCBJTlRPIGBmYWNlYm9va19hZGFwdGVyX21ldGFgIFZBTFVFUyAoJ2UyYjdkZDliLTFmMGEtNDViNS1hN2RjLThiYzhjOGQzOWYzZicsICdjYXB0aW9uJywgJ3ZhcmNoYXInKTsKSU5TRVJUIElOVE8gYGZhY2Vib29rX2FkYXB0ZXJfbWV0YWAgVkFMVUVTICgnZTJiN2RkOWItMWYwYS00NWI1LWE3ZGMtOGJjOGM4ZDM5ZjNmJywgJ3Byb3BlcnRpZXMnLCAndGV4dCcpOwpJTlNFUlQgSU5UTyBgZmFjZWJvb2tfYWRhcHRlcl9tZXRhYCBWQUxVRVMgKCdlMmI3ZGQ5Yi0xZjBhLTQ1YjUtYTdkYy04YmM4YzhkMzlmM2YnLCAnaWNvbicsICd2YXJjaGFyJyk7CklOU0VSVCBJTlRPIGBmYWNlYm9va19hZGFwdGVyX21ldGFgIFZBTFVFUyAoJ2UyYjdkZDliLTFmMGEtNDViNS1hN2RjLThiYzhjOGQzOWYzZicsICd0eXBlJywgJ3ZhcmNoYXInKTsKSU5TRVJUIElOVE8gYGZhY2Vib29rX2FkYXB0ZXJfbWV0YWAgVkFMVUVTICgnZTJiN2RkOWItMWYwYS00NWI1LWE3ZGMtOGJjOGM4ZDM5ZjNmJywgJ29iamVjdF9pZCcsICdpbnQnKTsKSU5TRVJUIElOVE8gYGZhY2Vib29rX2FkYXB0ZXJfbWV0YWAgVkFMVUVTICgnZTJiN2RkOWItMWYwYS00NWI1LWE3ZGMtOGJjOGM4ZDM5ZjNmJywgJ2FwcGxpY2F0aW9uX25hbWUnLCAndmFyY2hhcicpOwpJTlNFUlQgSU5UTyBgZmFjZWJvb2tfYWRhcHRlcl9tZXRhYCBWQUxVRVMgKCdlMmI3ZGQ5Yi0xZjBhLTQ1YjUtYTdkYy04YmM4YzhkMzlmM2YnLCAnYXBwbGljYXRpb25faWQnLCAnaW50Jyk7CklOU0VSVCBJTlRPIGBmYWNlYm9va19hZGFwdGVyX21ldGFgIFZBTFVFUyAoJ2UyYjdkZDliLTFmMGEtNDViNS1hN2RjLThiYzhjOGQzOWYzZicsICdjcmVhdGVkX3RpbWUnLCAnZGF0ZScpOwpJTlNFUlQgSU5UTyBgZmFjZWJvb2tfYWRhcHRlcl9tZXRhYCBWQUxVRVMgKCdlMmI3ZGQ5Yi0xZjBhLTQ1YjUtYTdkYy04YmM4YzhkMzlmM2YnLCAndXBkYXRlZF90aW1lJywgJ2RhdGUnKTs=' );
        
        $db = Zend_Registry::get( 'db' );
        
        $db->query( $tableData );
    }
    
    public function testNormalizationSuccess() {
        
        $params = array(
            'tables' => array(
                'e2b7dd9b-1f0a-45b5-a7dc-8bc8c8d39f3f' => array(
                    'columns' => array(
                        'from_name'    => '',
                        'story'        => '',
                        'properties'   => '',
                        'created_time' => '',
                        'updated_time' => 'count'
                    ),
                    'join'    => array(
                        'type'  => 'date',
                        'value' => 'created_time'
                    ),
                    'adapter' => 'facebook'
                ),
                'c03843e7-927d-4aef-88ca-370137ff3fb6' => array(
                    'columns' => array(
                        'name'  => '',
                        'query' => 'sum'
                    ),
                    'join'    => array(
                        'type'  => 'date',
                        'value' => 'date'
                    ),
                    'adapter' => 'twitter'
                )
            ),
            'user_id' => 10
        );
        
        $response = null;
        $ex       = null;
        try {
    
            $response = $this->handler->normalize_table_join( $params['user_id'], $params['tables'], $date_join_accuracy = NULL );
        } catch( Exception $ex ) {
            
            
        }
        
        $this->assertNull( $ex );
        $this->assertInternalType( 'array', $response );
        $this->assertEquals( 'success', $response['status'] );
        
        \Zend_Registry::get( 'db_normalizer' )->query( 'DROP TABLE `' . $response['response'] . '`' );
    }
    
    public function testNormalizationFailureInvalidTableData() {
        
        $params = array(
            'tables' => array(
                'e2b7dd9b-1f0a-45b5-a7dc-8bc8c8d39f3f' => array(
                    'columns' => array(
                        'from_name' => '', 
                        'story' =>  '',
                        'properties' => '', 
                        'created_time' => '',
                        'updated_time' => ''
                    ),
                    'join' => array(
                        'type' => 'date',
                        'value' => 'created_time'
                    ),
                    'adapter' => 'facebook'
                ),
                'invalidTable' => array(
                    'columns' => array(
                        'name' => '',
                        'query' => ''
                    ),
                    'join' => array(
                        'type' => 'date',
                        'value' => 'date'
                    ),
                    'adapter' => 'twitter'
                )
            ),
            'user_id' => 10
        );
        
        $ex = null;
        
        try {
            
            $this->handler->normalize_table_join( $params['user_id'], $params['tables'], $date_join_accuracy = NULL );
            
        } catch( \Exception $ex ) {
            
            
        }
        
        $this->assertNotNull( $ex );
    }
    
    public function testNormalizationFailureInvalidColumns() {
        
        $params = array(
            'tables' => array(
                'e2b7dd9b-1f0a-45b5-a7dc-8bc8c8d39f3f' => array(
                    'columns' => array(
                        'yra' => '', 
                        'story' =>  '',
                        'properties' => '', 
                        'created_time' => '',
                        'updated_time' => ''
                    ),
                    'join' => array(
                        'type' => 'date',
                        'value' => 'created_time'
                    ),
                    'adapter' => 'facebook'
                ),
                'c03843e7-927d-4aef-88ca-370137ff3fb6' => array(
                    'columns' => array(
                        'nx' => '',
                        'query' => ''
                    ),
                    'join' => array(
                        'type' => 'date',
                        'value' => 'date'
                    ),
                    'adapter' => 'twitter'
                )
            ),
            'user_id' => 10
        );
        
        $ex = null;
        
        try {
            
            $this->handler->normalize_table_join( $params['user_id'], $params['tables'], $date_join_accuracy = NULL );
            
        } catch( \Exception $ex ) {
            
            
        }
        
        $this->assertNotNull( $ex );
    }
    
    public function testNormalizationFailureNullParameters() {
        
        $ex = null;
        
        try {
            
            $this->handler->normalize_table_join( 10, null, $date_join_accuracy = NULL );
            
        } catch( \Exception $ex ) {
            
            
        }
        
        $this->assertNotNull( $ex );
    }
    
    public function tearDown()
    {
        $db = Zend_Registry::get( 'db' );
        $db->query( 'TRUNCATE TABLE twitter_adapter'       );
        $db->query( 'TRUNCATE TABLE twitter_adapter_data'  );
        $db->query( 'TRUNCATE TABLE twitter_adapter_meta'  );
        $db->query( 'TRUNCATE TABLE facebook_adapter'      );
        $db->query( 'TRUNCATE TABLE facebook_adapter_data' );
        $db->query( 'TRUNCATE TABLE facebook_adapter_meta' );
        parent::tearDown();
    }
}