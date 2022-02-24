{{/*
Expand the name of the chart.
*/}}
{{- define "ukpktest.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Create a default fully qualified app name.
We truncate at 63 chars because some Kubernetes name fields are limited to this (by the DNS naming spec).
If release name contains chart name it will be used as a full name.
*/}}
{{- define "db.url"}}{{- printf "mysql://%s:%s@%s:%s/%s?appVersion=5.7" "root" .Values.mysql.auth.rootPassword .Values.mysql.fullnameOverride (.Values.mysql.primary.service.port|toString) "%s" }}{{- end }}
{{- define "ukpktest.secret" -}}
{{- include "ukpktest.fullname" .}}-secret
{{- end }}
{{- define "ukpktest.main" -}}
{{- include "ukpktest.fullname" .}}-main
{{- end }}
{{- define "ukpktest.auth" -}}
{{- include "ukpktest.fullname" .}}-auth
{{- end }}
{{- define "ukpktest.logtail" -}}
{{- include "ukpktest.fullname" .}}-logtail
{{- end }}
{{- define "ukpktest.assets" -}}
{{- include "ukpktest.fullname" .}}-assets
{{- end }}
{{- define "ukpktest.fullname" -}}
{{- if .Values.fullnameOverride }}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- $name := default .Chart.Name .Values.nameOverride }}
{{- if contains $name .Release.Name }}
{{- .Release.Name | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- printf "%s-%s" .Release.Name $name | trunc 63 | trimSuffix "-" }}
{{- end }}
{{- end }}
{{- end }}

{{/*
Create chart name and version as used by the chart label.
*/}}
{{- define "ukpktest.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Common labels
*/}}
{{- define "ukpktest.labels" -}}
app: {{ include "ukpktest.fullname" . }}
version: {{ .Chart.Version }}
helm.sh/chart: {{ include "ukpktest.chart" . }}
{{ include "ukpktest.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{/*
Selector labels
*/}}
{{- define "ukpktest.selectorLabels" -}}
app.kubernetes.io/name: {{ include "ukpktest.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{/*
Create the name of the service account to use
*/}}
{{- define "ukpktest.serviceAccountName" -}}
{{- if .Values.serviceAccount.create }}
{{- default (include "ukpktest.fullname" .) .Values.serviceAccount.name }}
{{- else }}
{{- default "default" .Values.serviceAccount.name }}
{{- end }}
{{- end }}
