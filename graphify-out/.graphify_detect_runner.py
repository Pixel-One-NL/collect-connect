#!/usr/bin/env python3
"""Runner for graphify detect step executed via background_process to avoid shell quoting / timeout limits."""
import json
import sys
import time
from pathlib import Path

# Import graphify (assumes install already verified)
from graphify.detect import detect

print("BEGIN_DETECT", file=sys.stderr)
t0 = time.time()
result = detect(Path("."))
dt = time.time() - t0

# Write full result (as required by later steps)
out_json = Path("graphify-out/.graphify_detect.json")
out_json.write_text(json.dumps(result, ensure_ascii=False), encoding="utf-8")

# Write compact summary
summary = {
    "total_files": result.get("total_files", 0),
    "total_words": result.get("total_words", 0),
    "skipped_sensitive_count": len(result.get("skipped_sensitive", [])),
    "counts": {
        "code": len(result.get("files", {}).get("code", [])),
        "document": len(result.get("files", {}).get("document", [])),
        "paper": len(result.get("files", {}).get("paper", [])),
        "image": len(result.get("files", {}).get("image", [])),
        "video": len(result.get("files", {}).get("video", [])),
    },
    "scan_root": result.get("scan_root"),
}
Path("graphify-out/.graphify_detect_summary.json").write_text(json.dumps(summary, indent=2, ensure_ascii=False), encoding="utf-8")

print("DETECT_DONE", file=sys.stderr)
print("DETECT_TIME_SECONDS=" + str(round(dt, 1)), file=sys.stderr)
print(json.dumps(summary))
print("BG_DETECT_COMPLETE", file=sys.stderr)

# Also write done marker file for easy polling
Path("graphify-out/.detect_complete").write_text("ok\n", encoding="utf-8")

print("runner_exit=0")
